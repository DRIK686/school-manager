<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\ExamSchedule;
use App\Models\StudentMark;
use App\Models\GradeScale;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\SchoolSetting;
use App\Models\LessonPlan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ExamController extends Controller
{
    // Resolve which class a student was in during a given academic year.
    // Falls back to their current class if no historical enrollment record exists
    // (covers the common case: viewing a report card for the student's CURRENT year).
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

    // Exam Types
    public function index(Request $request)
    {
        $year = $request->academic_year_id
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::current();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $examTypes = ExamType::with('schedules')
            ->where('academic_year_id', $year?->id)
            ->latest()->get();
        $school    = \App\Models\SchoolSetting::first();
        $isTeacher = auth()->user()->isTeacher();
        if ($isTeacher) {
            return view('teacher.exams.index', compact('examTypes','year','school','academicYears'));
        }
        return view('admin.exams.index', compact('examTypes','year','academicYears'));
    }

    public function storeType(Request $request)
    {
        if (auth()->user()->isTeacher()) abort(403);
        $request->validate(['name' => 'required|string|max:100']);
        ExamType::create([
            'name'             => $request->name,
            'description'      => $request->description,
            'academic_year_id' => AcademicYear::current()?->id,
        ]);
        return back()->with('success', 'Exam type created.');
    }

    public function togglePublish(ExamType $exam)
    {
        if (auth()->user()->isTeacher()) abort(403);
        $exam->update(['is_published' => !$exam->is_published]);
        return back()->with('success', $exam->is_published ? 'Results published.' : 'Results unpublished.');
    }

    public function destroyType(ExamType $exam)
    {
        if (auth()->user()->isTeacher()) abort(403);
        $exam->delete();
        return back()->with('success', 'Exam type deleted.');
    }

    // Exam Schedule
    public function schedule(ExamType $exam)
    {
        if (auth()->user()->isTeacher()) abort(403, 'Teachers cannot manage exam schedules.');
        $schedules = $exam->schedules()->with(['schoolClass','subject'])->orderBy('exam_date')->get();
        $classes   = SchoolClass::orderBy('numeric_order')->get();
        $subjects  = Subject::orderBy('name')->get();

        // Subjects actually assigned to each class, for the dependent subject dropdown.
        $classSubjectsMap = \App\Models\ClassSubject::with('subject:id,name')->get()
            ->groupBy('class_id')
            ->map(fn($group) => $group->map(fn($cs) => ['id' => $cs->subject_id, 'name' => $cs->subject->name])->values());

        return view('admin.exams.schedule', compact('exam','schedules','classes','subjects','classSubjectsMap'));
    }

    public function storeSchedule(Request $request, ExamType $exam)
    {
        if (auth()->user()->isTeacher()) abort(403);
        $request->validate([
            'class_id'     => 'required|exists:classes,id',
            'subject_id'   => 'required|exists:subjects,id',
            'exam_date'    => 'nullable|date',
            'max_marks'    => 'required|numeric|min:1',
            'passing_marks'=> 'required|numeric|min:0',
        ]);

        $subjectAssigned = \App\Models\ClassSubject::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)->exists();
        if (!$subjectAssigned) {
            return back()->withErrors(['subject_id' => 'That subject is not assigned to the selected class.'])->withInput();
        }

        ExamSchedule::updateOrCreate(
            ['exam_type_id' => $exam->id, 'class_id' => $request->class_id, 'subject_id' => $request->subject_id],
            $request->only('exam_date','start_time','end_time','room','max_marks','passing_marks')
        );
        return back()->with('success', 'Schedule added.');
    }

    public function destroySchedule(ExamType $exam, ExamSchedule $schedule)
    {
        if (auth()->user()->isTeacher()) abort(403);
        $schedule->delete();
        return back()->with('success', 'Schedule removed.');
    }

    // Marks Entry
    public function marks(Request $request, ExamType $exam)
    {
        $user = auth()->user();
        if ($user->isTeacher()) {
            $myClassIds = \App\Models\ClassSubject::where('teacher_id', $user->id)->pluck('class_id')->unique();
            $classes = SchoolClass::whereIn('id', $myClassIds)->orderBy('numeric_order')->get();
        } else {
            $classes = SchoolClass::orderBy('numeric_order')->get();
        }
        $classId  = $request->class_id;
        $schedules= collect();
        $students = collect();

        if ($classId) {
            $schedules = $exam->schedules()->with('subject')
                ->where('class_id', $classId)->get();
            $students  = Student::where('class_id', $classId)
                ->where('is_active', true)->orderBy('first_name')->get();
        }

        $existingMarks = StudentMark::whereIn('exam_schedule_id', $schedules->pluck('id'))
            ->get()->keyBy(fn($m) => $m->student_id . '-' . $m->exam_schedule_id);

        $school    = \App\Models\SchoolSetting::first();
        $isTeacher = auth()->user()->isTeacher();
        $view      = $isTeacher ? 'teacher.exams.marks' : 'admin.exams.marks';
        return view($view, compact('exam','classes','classId','schedules','students','existingMarks','school'));
    }

    public function saveMarks(Request $request, ExamType $exam)
    {
        $year = AcademicYear::current();
        $user = auth()->user();

        // Authorization: a teacher may only enter marks for schedules
        // (class + subject) they are actually assigned to teach.
        if ($user->isTeacher()) {
            $scheduleIds = collect($request->marks ?? [])->flatMap(fn($s) => array_keys($s))->unique();
            $schedules = \App\Models\ExamSchedule::whereIn('id', $scheduleIds)->get(['id','class_id','subject_id']);
            foreach ($schedules as $schedule) {
                $assigned = \App\Models\ClassSubject::where('teacher_id', $user->id)
                    ->where('class_id', $schedule->class_id)
                    ->where('subject_id', $schedule->subject_id)
                    ->exists();
                abort_unless($assigned, 403, 'You are not assigned to this class/subject.');
            }
        }

        foreach ($request->marks ?? [] as $studentId => $scheduleMarks) {
            foreach ($scheduleMarks as $scheduleId => $markData) {
                $isAbsent = isset($request->absent[$studentId][$scheduleId]);

                // Handle both old single-value and new SBA+Exam format
                if (is_array($markData)) {
                    $sba  = $isAbsent ? null : (float)($markData['sba']  ?? 0);
                    $exam_score = $isAbsent ? null : (float)($markData['exam'] ?? 0);
                    $total = ($sba !== null && $exam_score !== null) ? $sba + $exam_score : null;
                } else {
                    $sba        = null;
                    $exam_score = null;
                    $total      = $isAbsent ? null : (float)($markData ?? 0);
                }

                $gradeObj = $total !== null ? GradeScale::getGrade($total, $year?->id) : null;

                StudentMark::updateOrCreate(
                    ['student_id' => $studentId, 'exam_schedule_id' => $scheduleId],
                    [
                        'sba_score'      => $sba,
                        'exam_score'     => $exam_score,
                        'marks_obtained' => $total,
                        'grade'          => $gradeObj?->grade,
                        'remarks'        => $gradeObj?->remark,
                        'is_absent'      => $isAbsent,
                    ]
                );
            }
        }

        // Compute class averages per schedule after saving all marks
        $classId = $request->class_id;
        $scheduleIds = collect($request->marks)->flatMap(fn($s) => array_keys($s))->unique();
        foreach ($scheduleIds as $scheduleId) {
            $avg = StudentMark::where('exam_schedule_id', $scheduleId)
                ->where('is_absent', false)
                ->whereNotNull('marks_obtained')
                ->avg('marks_obtained');
            if ($avg !== null) {
                StudentMark::where('exam_schedule_id', $scheduleId)
                    ->where('is_absent', false)
                    ->update(['class_average' => round($avg, 2)]);
            }
        }

        \App\Services\ActivityLogger::log('marks.save', 'Saved exam marks for class_id ' . $request->class_id . ', exam: ' . $exam->name);
        return back()->with('success', 'Marks saved successfully.');
    }
    public function results(ExamType $exam, Request $request)
    {
        $user = auth()->user();
        if ($user->isTeacher()) {
            $myClassIds = SchoolClass::where('class_teacher_id', $user->id)->pluck('id');
            $classes = SchoolClass::whereIn('id', $myClassIds)->orderBy('numeric_order')->get();
        } else {
            $classes = SchoolClass::orderBy('numeric_order')->get();
        }
        $classId  = $request->class_id;
        $results  = collect();

        if ($classId && $user->isTeacher()) {
            abort_unless($user->isClassTeacherOf($classId), 403, 'Only the class teacher may view compiled results for this class.');
        }

        if ($classId) {
            // $classId here is the HISTORICAL class the exam's schedules belong to.
            $schedules = $exam->schedules()->with('subject')->where('class_id',$classId)->get();

            // Students who were in that class during the exam's academic year:
            // either currently there (common case), or historically there per enrollment record.
            $currentIds    = Student::where('class_id',$classId)
                ->where('academic_year_id', $exam->academic_year_id)
                ->where('is_active',true)->pluck('id');
            $historicalIds = \App\Models\StudentEnrollment::where('class_id',$classId)
                ->where('academic_year_id', $exam->academic_year_id)->pluck('student_id');
            $studentIds = $currentIds->merge($historicalIds)->unique()->values();
            $students   = Student::whereIn('id', $studentIds)->orderBy('first_name')->get();

            $examYearId = $exam->academic_year_id;

            $liveAverages = StudentMark::whereIn('exam_schedule_id', $schedules->pluck('id'))
                ->where('is_absent', false)->whereNotNull('marks_obtained')
                ->selectRaw('exam_schedule_id, AVG(marks_obtained) as avg_marks')
                ->groupBy('exam_schedule_id')
                ->pluck('avg_marks', 'exam_schedule_id');

            $results = $students->map(function($student) use ($schedules, $exam, $examYearId, $liveAverages) {
                $marks = StudentMark::where('student_id',$student->id)
                    ->whereIn('exam_schedule_id',$schedules->pluck('id'))
                    ->get()->keyBy('exam_schedule_id');
                foreach ($marks as $scheduleId => $mark) {
                    $mark->class_average = isset($liveAverages[$scheduleId]) ? round($liveAverages[$scheduleId], 2) : null;
                }

                $total   = $marks->whereNotNull('marks_obtained')->sum('marks_obtained');
                $maxTotal= $schedules->sum('max_marks');
                $avg     = $schedules->count() > 0 ? round($total / max(1,$schedules->count()), 1) : 0;
                $year    = $examYearId ? AcademicYear::find($examYearId) : AcademicYear::current();
                $grade   = GradeScale::getGrade($avg, $year?->id);

                return [
                    'student'        => $student,
                    'marks'          => $marks,
                    'schedules'      => $schedules,
                    'total'          => $total,
                    'max_total'      => $maxTotal,
                    'avg'            => $avg,
                    'grade'          => $grade,
                    'total_students' => 0, // filled after sort
                ];
            })->sortByDesc('avg')->values();

            // Add position
            $totalStudents = $results->count();
            $results = $results->map(function($r, $i) use ($totalStudents) {
                $r['position']       = $i + 1;
                $r['total_students'] = $totalStudents;
                return $r;
            });
        }

        $school    = \App\Models\SchoolSetting::first();
        $isTeacher = auth()->user()->isTeacher();
        $view      = $isTeacher ? 'teacher.exams.results' : 'admin.exams.results';
        return view($view, compact('exam','classes','classId','results','school'));
    }

    // Report Card PDF
    public function reportCard(ExamType $exam, Student $student)
    {
        // Use the class the student was actually in during THIS exam's year,
        // not their current (possibly promoted) class.
        $historicalClassId = $this->classIdForYear($student, $exam->academic_year_id);

        // Authorization: only the class teacher (or admin) may view/finalize
        // compiled report cards for this class.
        $user = auth()->user();
        if ($user->isTeacher()) {
            abort_unless($user->isClassTeacherOf($historicalClassId), 403, 'Only the class teacher may view report cards for this class.');
        }
        $historicalClass    = SchoolClass::find($historicalClassId);

        $schedules = $exam->schedules()->with('subject')
            ->where('class_id', $historicalClassId)->orderBy('exam_date')->get();

        $marks = StudentMark::where('student_id', $student->id)
            ->whereIn('exam_schedule_id', $schedules->pluck('id'))
            ->get()->keyBy('exam_schedule_id');

        // Compute LIVE class averages per schedule (see note in results() — the
        // stored column can go stale after a mark correction).
        $liveAverages = StudentMark::whereIn('exam_schedule_id', $schedules->pluck('id'))
            ->where('is_absent', false)->whereNotNull('marks_obtained')
            ->selectRaw('exam_schedule_id, AVG(marks_obtained) as avg_marks')
            ->groupBy('exam_schedule_id')
            ->pluck('avg_marks', 'exam_schedule_id');
        foreach ($marks as $scheduleId => $mark) {
            $mark->class_average = isset($liveAverages[$scheduleId]) ? round($liveAverages[$scheduleId], 2) : null;
        }

        $total    = $marks->where('is_absent', false)->whereNotNull('marks_obtained')->sum('marks_obtained');
        $maxTotal = $schedules->sum('max_marks');
        $avg      = $schedules->count() > 0 ? round($total / max(1, $schedules->count()), 1) : 0;

        // Performance summary: this student's own best/worst subject this term
        $scoredMarks = $marks->where('is_absent', false)->whereNotNull('marks_obtained');
        $highestMark = $scoredMarks->max('marks_obtained');
        $lowestMark  = $scoredMarks->min('marks_obtained');

        $year     = $exam->academic_year_id ? AcademicYear::find($exam->academic_year_id) : AcademicYear::current();
        $grade    = GradeScale::getGrade($avg, $year?->id);
        $school   = SchoolSetting::current();
        $student->load(['schoolClass','section','academicYear']);

        // Get position in class — ranked against classmates from THAT year, not current classmates
        $currentMatches    = Student::where('academic_year_id', $exam->academic_year_id)
            ->where('class_id', $historicalClassId)->where('is_active', true)->pluck('id');
        $historicalMatches = \App\Models\StudentEnrollment::where('academic_year_id', $exam->academic_year_id)
            ->where('class_id', $historicalClassId)->pluck('student_id');
        $classStudents  = $currentMatches->merge($historicalMatches)->unique()->values();
        $total_students = $classStudents->count();
        $allAverages     = StudentMark::whereIn('student_id', $classStudents)
            ->whereIn('exam_schedule_id', $schedules->pluck('id'))
            ->where('is_absent', false)
            ->selectRaw('student_id, SUM(marks_obtained) as total')
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->pluck('student_id')->values();
        $position = $allAverages->search($student->id);
        $position = $position !== false ? $position + 1 : null;

        // Load term report (conduct, remarks etc)
        $termReport = \App\Models\StudentTermReport::where('student_id', $student->id)
            ->where('exam_type_id', $exam->id)
            ->where('academic_year_id', $year?->id)
            ->first();

        // Term-by-term comparison: this student's overall average across every
        // published exam type in the same academic year, for the progress chart.
        $termComparison = ExamType::where('academic_year_id', $exam->academic_year_id)
            ->where('is_published', true)
            ->orderBy('created_at')
            ->get()
            ->map(function ($termExam) use ($student, $historicalClassId, $exam) {
                $termSchedules = $termExam->schedules()->where('class_id', $historicalClassId)->get();
                if ($termSchedules->isEmpty()) {
                    return null;
                }
                $termMarks = StudentMark::where('student_id', $student->id)
                    ->whereIn('exam_schedule_id', $termSchedules->pluck('id'))
                    ->where('is_absent', false)->whereNotNull('marks_obtained')
                    ->sum('marks_obtained');
                $termAvg = round($termMarks / max(1, $termSchedules->count()), 1);
                return ['name' => $termExam->name, 'avg' => $termAvg, 'is_current' => $termExam->id === $exam->id];
            })
            ->filter()
            ->values();

        $pdf = Pdf::loadView('admin.exams.report_card_pdf', compact(
            'exam','student','schedules','marks',
            'total','maxTotal','avg','grade','highestMark','lowestMark',
            'school','year','position','total_students','termReport','historicalClass','termComparison'
        ))->setPaper('a4','portrait');

        return $pdf->download(str_replace('/', '-', "report-card-{$student->admission_no}-{$exam->name}.pdf"));
    }

    // Email the same report card PDF to the parent on file, instead of downloading it.
    public function emailReportCard(ExamType $exam, Student $student)
    {
        $historicalClassId = $this->classIdForYear($student, $exam->academic_year_id);
        $user = auth()->user();
        if ($user->isTeacher()) {
            abort_unless($user->isClassTeacherOf($historicalClassId), 403, 'Only the class teacher may email report cards for this class.');
        }

        $parent = \App\Models\StudentParent::where('student_id', $student->id)
            ->whereNotNull('email')->first();
        if (!$parent) {
            return back()->with('error', 'No parent email on file for ' . $student->full_name . '.');
        }

        $pdf = $this->buildReportCardPdfForEmail($exam, $student);

        try {
            \App\Services\MailConfigService::applyFromSettings();
            $student->load('schoolClass');
            \Illuminate\Support\Facades\Mail::to($parent->email)
                ->send(new \App\Mail\ReportCardMail($student, $exam, $parent, $pdf->output()));
            \App\Services\ActivityLogger::log('report-card.email', 'Emailed report card for ' . $student->full_name . ' (' . $exam->name . ') to ' . $parent->email . '.');
            return back()->with('success', 'Report card emailed to ' . $parent->email . '.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Report card email failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to send email. Please check SMTP settings.');
        }
    }

    // Builds the same report card PDF as reportCard(), for use when emailing
    // rather than downloading. Kept separate from reportCard() to avoid
    // touching the existing, already-working download path.
    private function buildReportCardPdfForEmail(ExamType $exam, Student $student)
    {
        $historicalClassId = $this->classIdForYear($student, $exam->academic_year_id);
        $historicalClass    = SchoolClass::find($historicalClassId);

        $schedules = $exam->schedules()->with('subject')
            ->where('class_id', $historicalClassId)->orderBy('exam_date')->get();

        $marks = StudentMark::where('student_id', $student->id)
            ->whereIn('exam_schedule_id', $schedules->pluck('id'))
            ->get()->keyBy('exam_schedule_id');

        $liveAverages = StudentMark::whereIn('exam_schedule_id', $schedules->pluck('id'))
            ->where('is_absent', false)->whereNotNull('marks_obtained')
            ->selectRaw('exam_schedule_id, AVG(marks_obtained) as avg_marks')
            ->groupBy('exam_schedule_id')
            ->pluck('avg_marks', 'exam_schedule_id');
        foreach ($marks as $scheduleId => $mark) {
            $mark->class_average = isset($liveAverages[$scheduleId]) ? round($liveAverages[$scheduleId], 2) : null;
        }

        $total    = $marks->where('is_absent', false)->whereNotNull('marks_obtained')->sum('marks_obtained');
        $maxTotal = $schedules->sum('max_marks');
        $avg      = $schedules->count() > 0 ? round($total / max(1, $schedules->count()), 1) : 0;

        $scoredMarks = $marks->where('is_absent', false)->whereNotNull('marks_obtained');
        $highestMark = $scoredMarks->max('marks_obtained');
        $lowestMark  = $scoredMarks->min('marks_obtained');

        $year     = $exam->academic_year_id ? AcademicYear::find($exam->academic_year_id) : AcademicYear::current();
        $grade    = GradeScale::getGrade($avg, $year?->id);
        $school   = SchoolSetting::current();
        $student->load(['schoolClass','section','academicYear']);

        $currentMatches    = Student::where('academic_year_id', $exam->academic_year_id)
            ->where('class_id', $historicalClassId)->where('is_active', true)->pluck('id');
        $historicalMatches = \App\Models\StudentEnrollment::where('academic_year_id', $exam->academic_year_id)
            ->where('class_id', $historicalClassId)->pluck('student_id');
        $classStudents  = $currentMatches->merge($historicalMatches)->unique()->values();
        $total_students = $classStudents->count();
        $allAverages     = StudentMark::whereIn('student_id', $classStudents)
            ->whereIn('exam_schedule_id', $schedules->pluck('id'))
            ->where('is_absent', false)
            ->selectRaw('student_id, SUM(marks_obtained) as total')
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->pluck('student_id')->values();
        $position = $allAverages->search($student->id);
        $position = $position !== false ? $position + 1 : null;

        $termReport = \App\Models\StudentTermReport::where('student_id', $student->id)
            ->where('exam_type_id', $exam->id)
            ->where('academic_year_id', $year?->id)
            ->first();

        $termComparison = ExamType::where('academic_year_id', $exam->academic_year_id)
            ->where('is_published', true)
            ->orderBy('created_at')
            ->get()
            ->map(function ($termExam) use ($student, $historicalClassId, $exam) {
                $termSchedules = $termExam->schedules()->where('class_id', $historicalClassId)->get();
                if ($termSchedules->isEmpty()) {
                    return null;
                }
                $termMarks = StudentMark::where('student_id', $student->id)
                    ->whereIn('exam_schedule_id', $termSchedules->pluck('id'))
                    ->where('is_absent', false)->whereNotNull('marks_obtained')
                    ->sum('marks_obtained');
                $termAvg = round($termMarks / max(1, $termSchedules->count()), 1);
                return ['name' => $termExam->name, 'avg' => $termAvg, 'is_current' => $termExam->id === $exam->id];
            })
            ->filter()
            ->values();

        return Pdf::loadView('admin.exams.report_card_pdf', compact(
            'exam','student','schedules','marks',
            'total','maxTotal','avg','grade','highestMark','lowestMark',
            'school','year','position','total_students','termReport','historicalClass','termComparison'
        ))->setPaper('a4','portrait');
    }

    // Grade Scales
    public function gradeScales()
    {
        $year   = AcademicYear::current();
        $scales = GradeScale::where('academic_year_id',$year?->id)->orderByDesc('min_mark')->get();
        return view('admin.exams.grades', compact('scales','year'));
    }

    public function storeGrade(Request $request)
    {
        $request->validate([
            'grade'     => 'required|string|max:5',
            'min_mark'  => 'required|numeric|min:0|max:100',
            'max_mark'  => 'required|numeric|min:0|max:100|gte:min_mark',
            'points'    => 'required|integer|min:1',
            'remark'    => 'nullable|string|max:50',
        ]);
        GradeScale::create(array_merge(
            $request->only('grade','min_mark','max_mark','points','remark'),
            ['academic_year_id' => AcademicYear::current()?->id]
        ));
        return back()->with('success', 'Grade scale added.');
    }

    public function destroyGrade(GradeScale $scale)
    {
        $scale->delete();
        return back()->with('success', 'Grade removed.');
    }

    // ── Admin: Lesson Plan Review ────────────────────────────────
    public function lessonPlansAdmin(Request $request)
    {
        $school = \App\Models\SchoolSetting::first();
        $plans  = LessonPlan::with(['teacher','schoolClass','subject'])
            ->when($request->status,  fn($q) => $q->where('status',  $request->status))
            ->when($request->teacher_id, fn($q) => $q->where('teacher_id', $request->teacher_id))
            ->orderByDesc('week_no')->orderBy('id')
            ->get();
        $teachers = \App\Models\User::whereHas('role', fn($q) => $q->where('slug','teacher'))->get();
        return view('admin.lesson_plans.index', compact('school','plans','teachers'));
    }

    public function reviewLessonPlan(Request $request, LessonPlan $plan)
    {
        $request->validate([
            'action'        => 'required|in:approve,return',
            'admin_remarks' => $request->action === 'return' ? 'required|string' : 'nullable|string',
        ]);
        if ($request->action === 'return') {
            $plan->update(['status' => 'needs_revision', 'admin_remarks' => $request->admin_remarks]);
            return back()->with('success', 'Lesson plan sent back to the teacher for correction.');
        }
        $plan->update(['status' => 'reviewed', 'admin_remarks' => $request->admin_remarks]);
        return back()->with('success', 'Lesson plan marked as reviewed.');
    }


    // Save Term Report (conduct, remarks etc)
    public function saveTermReport(Request $request, ExamType $exam, Student $student)
    {
        $year = AcademicYear::current();
        \App\Models\StudentTermReport::updateOrCreate(
            [
                'student_id'       => $student->id,
                'exam_type_id'     => $exam->id,
                'academic_year_id' => $year?->id,
            ],
            $request->only([
                'conduct','talent_interest','teacher_remarks','promoted_to',
                'attendance_present','attendance_total',
                'vacation_date','reopening_date',
                'class_teacher','principal',
            ])
        );
        return back()->with('success', 'Term report saved for ' . $student->full_name . '.');
    }

}
