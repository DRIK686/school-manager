<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Models\Student;
use App\Models\StudentPin;
use App\Models\AcademicYear;
use App\Models\SchoolSetting;
use App\Models\Notice;
use App\Models\Homework;
use App\Models\StudentAttendance;
use App\Models\FeePayment;
use App\Models\ExamType;
use App\Services\ExamsPracticeSsoService;
use App\Models\StudentMark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    // Resolve which class a student was in during a given academic year.
    private function classIdForYear(Student $student, ?int $academicYearId): int
    {
        if (!$academicYearId || $academicYearId === $student->academic_year_id) {
            return $student->class_id;
        }
        $enrollment = \App\Models\StudentEnrollment::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->first();
        return $enrollment?->class_id ?? $student->class_id;
    }

    // ── Auth ─────────────────────────────────────────────────────
    public function showLogin()
    {
        if (session('student_id')) return redirect()->route('student.dashboard');
        $school = SchoolSetting::first();
        return view('student.login', compact('school'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'admission_no' => 'required|string',
            'pin'          => 'required|string',
        ]);

        $student = Student::where('admission_no', $request->admission_no)
            ->where('is_active', true)
            ->first();

        if (!$student || !$student->pin || !Hash::check($request->pin, $student->pin->pin)) {
            ActivityLogger::log('login_failed', 'Failed student login attempt for admission no: ' . $request->admission_no);
            return back()->withErrors(['admission_no' => 'Invalid admission number or PIN.'])->onlyInput('admission_no');
        }

        // Store in session
        session(['student_id' => $student->id]);
        $student->pin->update(['last_login_at' => now()]);
        ActivityLogger::log('login', $student->full_name . ' (student) logged in.', null, $student);

        // First login — force PIN change
        if ($student->pin->is_first_login) {
            return redirect()->route('student.change-pin');
        }

        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request)
    {
        $student = $this->currentStudent();
        ActivityLogger::log('logout', $student->full_name . ' (student) logged out.', null, $student);
        $request->session()->forget('student_id');
        return redirect()->route('student.login');
    }
    public function profile()
    {
        $school  = SchoolSetting::first();
        $student = $this->currentStudent();
        return view('student.profile', compact('school','student'));
    }

    public function showChangePin()
    {
        $school   = SchoolSetting::first();
        $student  = $this->currentStudent();
        return view('student.change_pin', compact('school','student'));
    }

    public function changePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required',
            'new_pin'     => 'required|digits:4|confirmed',
        ]);

        $student = $this->currentStudent();

        if (!Hash::check($request->current_pin, $student->pin->pin)) {
            return back()->withErrors(['current_pin' => 'Current PIN is incorrect.']);
        }

        $student->pin->update([
            'pin'            => bcrypt($request->new_pin),
            'plain_pin'      => '',
            'is_first_login' => false,
        ]);

        return redirect()->route('student.dashboard')->with('success', 'PIN changed successfully.');
    }

    // ── Dashboard ─────────────────────────────────────────────────
    public function dashboard()
    {
        $student  = $this->currentStudent();
        $school   = SchoolSetting::first();
        $year     = AcademicYear::current();

        $notices  = Notice::active()->where(fn($q) => $q->where('audience','all')->orWhere('audience','students'))
            ->latest()->take(5)->get();

        $homework = Homework::where('class_id', $student->class_id)
            ->where('due_date', '>=', today())
            ->with(['subject','teacher'])
            ->orderBy('due_date')->take(5)->get();

        $attendanceSummary = StudentAttendance::where('student_id', $student->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total','status');

        $totalFeesPaid = FeePayment::where('student_id', $student->id)->sum('amount_paid');

        $recentResults = ExamType::where('is_published', true)
            ->where('academic_year_id', $year?->id)
            ->latest()->take(3)->get();

        return view('student.dashboard', compact(
            'student','school','year','notices','homework',
            'attendanceSummary','totalFeesPaid','recentResults'
        ));
    }

    // ── Fees ─────────────────────────────────────────────────────
    public function fees()
    {
        $student  = $this->currentStudent();
        $school   = SchoolSetting::first();
        $year     = AcademicYear::current();

        // Load fee structures for student's class with their payments
        $structures = \App\Models\FeeStructure::with([
                'feeCategory',
                'payments' => fn($q) => $q->where('student_id', $student->id)
            ])
            ->where('academic_year_id', $year?->id)
            ->where(function($q) use ($student) {
                $q->where('class_id', $student->class_id)->orWhereNull('class_id');
            })
            ->get()
            ->map(function($s) {
                $paid          = $s->payments->sum('amount_paid');
                $balance       = max(0, $s->amount - $paid);
                $s->paid_amount = $paid;
                $s->balance     = $balance;
                $s->status      = $balance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
                return $s;
            });

        $totalFees       = $structures->sum('amount');
        $totalPaid       = $structures->sum('paid_amount');
        $totalBalance    = $structures->sum('balance');
        $totalOverpaid   = FeePayment::where('student_id', $student->id)
                            ->where('academic_year_id', $year?->id)->sum('amount_paid') - $totalPaid;

        // Full payment history
        $payments = FeePayment::where('student_id', $student->id)
            ->with('feeStructure.feeCategory')
            ->latest()->get();

        return view('student.fees', compact(
            'student','school','year','structures',
            'totalFees','totalPaid','totalBalance','payments'
        ));
    }

    // ── Attendance ───────────────────────────────────────────────
    public function attendance(Request $request)
    {
        $student = $this->currentStudent();
        $school  = SchoolSetting::first();
        $month   = $request->month ?? date('m');
        $year    = $request->year  ?? date('Y');

        $records = StudentAttendance::where('student_id', $student->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get()
            ->keyBy(fn($r) => \Carbon\Carbon::parse($r->date)->format('j'));

        $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $summary     = StudentAttendance::where('student_id', $student->id)
            ->selectRaw('status, count(*) as total')->groupBy('status')
            ->pluck('total','status');

        return view('student.attendance', compact(
            'student','school','records','month','year','daysInMonth','summary'
        ));
    }

    // ── Results ──────────────────────────────────────────────────
    public function results(Request $request)
    {
        $student = $this->currentStudent();
        $school  = SchoolSetting::first();

        // Only offer years the student has actually been enrolled in
        // (their current year, plus any historical enrollment years).
        $enrolledYearIds = \App\Models\StudentEnrollment::where('student_id', $student->id)
            ->pluck('academic_year_id')
            ->push($student->academic_year_id)
            ->unique();
        $academicYears = AcademicYear::whereIn('id', $enrolledYearIds)
            ->orderByDesc('start_date')->get();

        $year = $request->academic_year_id
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::current();

        $examTypes = ExamType::where('is_published', true)
            ->where('academic_year_id', $year?->id)
            ->with(['schedules.subject'])
            ->latest()->get();

        $examId    = $request->exam_id ?? $examTypes->first()?->id;
        $marks     = collect();

        if ($examId) {
            $marks = StudentMark::where('student_id', $student->id)
                ->whereHas('examSchedule', fn($q) => $q->where('exam_type_id', $examId))
                ->with(['examSchedule.subject'])
                ->orderBy('exam_schedule_id')
                ->get();

            // Compute LIVE class averages per schedule (the stored/cached column
            // can go stale if a mark is corrected without every schedule's
            // average being recomputed in that same save).
            $liveAverages = StudentMark::whereIn('exam_schedule_id', $marks->pluck('exam_schedule_id'))
                ->where('is_absent', false)->whereNotNull('marks_obtained')
                ->selectRaw('exam_schedule_id, AVG(marks_obtained) as avg_marks')
                ->groupBy('exam_schedule_id')
                ->pluck('avg_marks', 'exam_schedule_id');
            foreach ($marks as $mark) {
                $mark->class_average = isset($liveAverages[$mark->exam_schedule_id]) ? round($liveAverages[$mark->exam_schedule_id], 2) : null;

                // Stored grade/remark can be empty (marks saved before grade scales were fixed).
                // Fall back to a live lookup in the exam's own year so students never see blanks.
                if (!$mark->is_absent && $mark->marks_obtained !== null && empty($mark->grade)) {
                    $maxMarks = (float) ($mark->examSchedule->max_marks ?? 100);
                    if ($maxMarks > 0) {
                        $gs = \App\Models\GradeScale::getGrade($mark->marks_obtained / $maxMarks * 100, $year?->id);
                        if ($gs) {
                            $mark->grade = $gs->grade;
                            if (empty($mark->remarks)) {
                                $mark->remarks = $gs->getAttribute('remark') ?? $gs->getAttribute('remarks');
                            }
                        }
                    }
                }
            }
        }

        return view('student.results', compact(
            'student','school','examTypes','examId','marks','academicYears','year'
        ));
    }

    // ── Homework ─────────────────────────────────────────────────
    public function homework()
    {
        $student  = $this->currentStudent();
        $school   = SchoolSetting::first();
        $homework = Homework::where('class_id', $student->class_id)
            ->with([
                'subject','teacher',
                'submissions' => fn($q) => $q->where('student_id', $student->id)
                    ->with('gradedBy')
            ])
            ->orderByDesc('due_date')->get();
        return view('student.homework', compact('student','school','homework'));
    }

    // ── Notices ──────────────────────────────────────────────────
    public function notices()
    {
        $student = $this->currentStudent();
        $school  = SchoolSetting::first();
        $notices = Notice::active()
            ->where(fn($q) => $q->where('audience','all')->orWhere('audience','students'))
            ->latest()->get();
        return view('student.notices', compact('student','school','notices'));
    }

    // ── Timetable ────────────────────────────────────────────────
    public function timetable()
    {
        $student   = $this->currentStudent();
        $school    = SchoolSetting::first();
        $year      = AcademicYear::current();
        $slots     = \App\Models\TimetableSlot::orderBy('slot_no')->get();
        $days      = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
        $grid      = [];

        if ($year) {
            $entries = \App\Models\Timetable::where('academic_year_id', $year->id)
                ->where('class_id', $student->class_id)
                ->with(['subject','teacher','slot'])
                ->get();

            foreach ($days as $dayNo => $dayName) {
                foreach ($slots as $slot) {
                    $grid[$dayNo][$slot->id] = $entries->first(
                        fn($e) => $e->day_of_week == $dayNo && $e->slot_id == $slot->id
                    );
                }
            }
        }

        return view('student.timetable', compact('student','school','slots','days','grid'));
    }

    // ── Exam Timetable ───────────────────────────────────────────
    public function examTimetable()
    {
        $student = $this->currentStudent();
        $school  = SchoolSetting::first();
        $year    = AcademicYear::current();

        $examTypes = collect();
        if ($year) {
            $examTypes = \App\Models\ExamType::where('academic_year_id', $year->id)
                ->where('is_published', true)
                ->orderBy('created_at')
                ->get()
                ->map(function ($exam) use ($student) {
                    $schedules = $exam->schedules()->with('subject')
                        ->where('class_id', $student->class_id)
                        ->orderBy('exam_date')->orderBy('start_time')
                        ->get();
                    return ['exam' => $exam, 'schedules' => $schedules];
                })
                ->filter(fn($e) => $e['schedules']->count() > 0)
                ->values();
        }

        return view('student.exam_timetable', compact('student','school','year','examTypes'));
    }


    // ── Report Card ──────────────────────────────────────────────
    public function reportCard(\App\Models\ExamType $exam)
    {
        $student = $this->currentStudent();

        // Only allow downloading published exams
        if (!$exam->is_published) {
            return back()->with('error', 'This report card is not yet available.');
        }

        $school = \App\Models\SchoolSetting::first();
        $year   = $exam->academic_year_id
            ? \App\Models\AcademicYear::find($exam->academic_year_id)
            : \App\Models\AcademicYear::current();

        $historicalClassId = $this->classIdForYear($student, $exam->academic_year_id);
        $historicalClass   = \App\Models\SchoolClass::find($historicalClassId);

        $schedules = $exam->schedules()->with('subject')
            ->where('class_id', $historicalClassId)
            ->orderBy('exam_date')->get();

        $marks = \App\Models\StudentMark::where('student_id', $student->id)
            ->whereIn('exam_schedule_id', $schedules->pluck('id'))
            ->get()->keyBy('exam_schedule_id');

        $total    = $marks->whereNotNull('marks_obtained')->sum('marks_obtained');
        $maxTotal = $schedules->sum('max_marks');
        $avg      = $schedules->count() > 0 ? round($total / max(1, $schedules->count()), 1) : 0;
        $grade    = \App\Models\GradeScale::getGrade($avg, $year?->id);

        // Best / weakest subject for the performance summary (same as the admin report card)
        $scoredMarks = $marks->where('is_absent', false)->whereNotNull('marks_obtained');
        $highestMark = $scoredMarks->max('marks_obtained');
        $lowestMark  = $scoredMarks->min('marks_obtained');

        // Live class averages per subject
        $liveAverages = \App\Models\StudentMark::whereIn('exam_schedule_id', $schedules->pluck('id'))
            ->where('is_absent', false)->whereNotNull('marks_obtained')
            ->selectRaw('exam_schedule_id, AVG(marks_obtained) as avg_marks')
            ->groupBy('exam_schedule_id')
            ->pluck('avg_marks', 'exam_schedule_id');
        foreach ($marks as $scheduleId => $mark) {
            $mark->class_average = isset($liveAverages[$scheduleId]) ? round($liveAverages[$scheduleId], 2) : null;
        }

        $student->load(['schoolClass','section','academicYear']);

        // Get position in class — ranked against classmates from THAT year
        $currentMatches    = \App\Models\Student::where('academic_year_id', $exam->academic_year_id)
            ->where('class_id', $historicalClassId)->where('is_active', true)->pluck('id');
        $historicalMatches = \App\Models\StudentEnrollment::where('academic_year_id', $exam->academic_year_id)
            ->where('class_id', $historicalClassId)->pluck('student_id');
        $classStudents = $currentMatches->merge($historicalMatches)->unique()->values();

        $allAverages = \App\Models\StudentMark::whereIn('student_id', $classStudents)
            ->whereIn('exam_schedule_id', $schedules->pluck('id'))
            ->selectRaw('student_id, SUM(marks_obtained) as total')
            ->groupBy('student_id')
            ->orderByDesc('total')->pluck('student_id')->values();

        $position = $allAverages->search($student->id);
        $position = $position !== false ? $position + 1 : null;

        $total_students = $allAverages->count();

        $termReport = \App\Models\StudentTermReport::where('student_id', $student->id)
            ->where('exam_type_id', $exam->id)
            ->where('academic_year_id', $year?->id)
            ->first();

        // Term-by-term progress for the chart on the report card
        $termComparison = \App\Models\ExamType::where('academic_year_id', $exam->academic_year_id)
            ->where('is_published', true)
            ->orderBy('created_at')
            ->get()
            ->map(function ($termExam) use ($student, $historicalClassId, $exam) {
                $termSchedules = $termExam->schedules()->where('class_id', $historicalClassId)->get();
                if ($termSchedules->isEmpty()) {
                    return null;
                }
                $termMarks = \App\Models\StudentMark::where('student_id', $student->id)
                    ->whereIn('exam_schedule_id', $termSchedules->pluck('id'))
                    ->where('is_absent', false)->whereNotNull('marks_obtained')
                    ->sum('marks_obtained');
                $termAvg = round($termMarks / max(1, $termSchedules->count()), 1);
                return ['name' => $termExam->name, 'avg' => $termAvg, 'is_current' => $termExam->id === $exam->id];
            })
            ->filter()
            ->values();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.exams.report_card_pdf', compact(
            'exam','student','schedules','marks','total','maxTotal','avg','grade','highestMark','lowestMark','termComparison',
            'school','year','position','total_students','termReport','historicalClass'
        ))->setPaper('a4','portrait');

        $filename = str_replace('/', '-', $student->admission_no) . '-' . str_replace(' ', '-', $exam->name) . '.pdf';
        return $pdf->download($filename);
    }


    // ── Submit Homework ───────────────────────────────────────────
    public function submitHomework(\Illuminate\Http\Request $request, \App\Models\Homework $homework)
    {
        $student = $this->currentStudent();

        // Check student belongs to this homework's class
        if ($homework->class_id !== $student->class_id) abort(403);

        $request->validate(['answer' => 'required|string|min:5']);

        \App\Models\HomeworkSubmission::updateOrCreate(
            ['homework_id' => $homework->id, 'student_id' => $student->id],
            [
                'answer'       => $request->answer,
                'status'       => 'submitted',
                'submitted_at' => now(),
                'score'        => null,
                'feedback'     => null,
                'graded_by'    => null,
                'graded_at'    => null,
            ]
        );

        return back()->with('success', 'Homework submitted successfully!');
    }

    // ── Exams Practice SSO ──────────────────────────────────────
    public function examsPracticeLaunch(ExamsPracticeSsoService $sso)
    {
        $student = $this->currentStudent();
        return redirect()->away($sso->launchUrl($student));
    }

    // ── Helper ───────────────────────────────────────────────────
    private function currentStudent(): Student
    {
        $id = session('student_id');
        if (!$id) abort(redirect()->route('student.login'));
        return Student::with(['schoolClass','section','pin'])->findOrFail($id);
    }
}
