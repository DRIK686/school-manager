<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\ExamType;
use App\Models\StudentAttendance;
use App\Models\LessonPlan;
use App\Models\AcademicYear;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use App\Models\PromotionRecommendation;
use App\Models\SchoolClass;

class TeacherController extends Controller
{
    private function myClassIds()
    {
        return ClassSubject::where('teacher_id', auth()->id())
            ->pluck('class_id')->unique()->values();
    }

    // Classes where this teacher is specifically the CLASS teacher (not just a subject teacher).
    // Used to gate class-administration actions: promotion, attendance, report cards.
    private function myClassTeacherClassIds()
    {
        return SchoolClass::where('class_teacher_id', auth()->id())
            ->pluck('id')->unique()->values();
    }

    private function mySubjects()
    {
        return ClassSubject::where('teacher_id', auth()->id())
            ->with(['schoolClass','subject'])->get();
    }

    // ── Dashboard ────────────────────────────────────────────────
    public function dashboard()
    {
        $school      = SchoolSetting::first();
        $year        = AcademicYear::current();
        $mySubjects  = $this->mySubjects();
        $myClassIds  = $mySubjects->pluck('class_id')->unique();

        $totalStudents = Student::whereIn('class_id', $myClassIds)
            ->where('is_active', true)->count();

        $todayAttendance = StudentAttendance::whereIn('class_id', $myClassIds)
            ->whereDate('date', today())->count();

        $pendingPlans = LessonPlan::where('teacher_id', auth()->id())
            ->where('status','draft')->count();

        $upcomingExams = ExamType::where('academic_year_id', $year?->id)
            ->where('is_published', false)
            ->latest()->take(3)->get();

        return view('teacher.dashboard', compact(
            'school','year','mySubjects','totalStudents',
            'todayAttendance','pendingPlans','upcomingExams'
        ));
    }

    // ── My Classes ───────────────────────────────────────────────
    public function myClasses()
    {
        $school     = SchoolSetting::first();
        $mySubjects = $this->mySubjects();
        // Group by class
        $byClass = $mySubjects->groupBy('class_id');
        return view('teacher.my_classes', compact('school','byClass','mySubjects'));
    }

    // ── My Students ──────────────────────────────────────────────
    public function myStudents(Request $request)
    {
        $school     = SchoolSetting::first();
        $myClassIds = $this->myClassIds();
        $query      = Student::whereIn('class_id', $myClassIds)
            ->where('is_active', true)
            ->with(['schoolClass','section']);

        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        $students   = $query->orderBy('first_name')->get();
        $mySubjects = $this->mySubjects();
        return view('teacher.my_students', compact('school','students','mySubjects','myClassIds'));
    }

    // ── Lesson Plans ─────────────────────────────────────────────
    public function lessonPlans(Request $request)
    {
        $school = SchoolSetting::first();
        $year   = AcademicYear::current();
        $plans  = LessonPlan::where('teacher_id', auth()->id())
            ->with(['schoolClass','subject'])
            ->when($request->week_no, fn($q) => $q->where('week_no', $request->week_no))
            ->when($request->status,  fn($q) => $q->where('status',  $request->status))
            ->orderByDesc('week_no')->orderBy('id')
            ->get();

        $mySubjects = $this->mySubjects();
        return view('teacher.lesson_plans.index', compact('school','year','plans','mySubjects'));
    }

    public function createLessonPlan()
    {
        $school     = SchoolSetting::first();
        $year       = AcademicYear::current();
        $mySubjects = $this->mySubjects();
        return view('teacher.lesson_plans.create', compact('school','year','mySubjects'));
    }

    public function storeLessonPlan(Request $request)
    {
        $year = AcademicYear::current();
        $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'week_no'    => 'required|integer|min:1|max:52',
            'topic'      => 'required|string|max:255',
            'objectives' => 'nullable|string',
            'activities' => 'nullable|string',
            'resources'  => 'nullable|string',
            'status'     => 'required|in:draft,submitted',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);
        // Ensure teacher is assigned to this class+subject
        $assigned = ClassSubject::where('teacher_id', auth()->id())
            ->where('class_id',   $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->exists();
        if (!$assigned) {
            return back()->withErrors(['subject_id' => 'You are not assigned to this subject/class.']);
        }
        $data = [
            'teacher_id'       => auth()->id(),
            'academic_year_id' => $year?->id,
            'class_id'         => $request->class_id,
            'subject_id'       => $request->subject_id,
            'week_no'          => $request->week_no,
            'topic'            => $request->topic,
            'objectives'       => $request->objectives,
            'activities'       => $request->activities,
            'resources'        => $request->resources,
            'status'           => $request->status,
        ];
        if ($request->hasFile('file')) {
            $data['file_path']          = $request->file('file')->store('lesson-plans', 'public');
            $data['file_original_name'] = $request->file('file')->getClientOriginalName();
        }
        LessonPlan::create($data);
        return redirect()->route('teacher.lesson-plans.index')
            ->with('success', 'Lesson plan saved.');
    }
    public function editLessonPlan(LessonPlan $plan)
    {
        abort_if($plan->teacher_id !== auth()->id(), 403);
        abort_if($plan->status === 'reviewed', 403, 'Reviewed plans cannot be edited.');
        $school     = SchoolSetting::first();
        $year       = AcademicYear::current();
        $mySubjects = $this->mySubjects();
        return view('teacher.lesson_plans.edit', compact('school','year','plan','mySubjects'));
    }
    public function updateLessonPlan(Request $request, LessonPlan $plan)
    {
        abort_if($plan->teacher_id !== auth()->id(), 403);
        abort_if($plan->status === 'reviewed', 403);
        $request->validate([
            'week_no'    => 'required|integer|min:1|max:52',
            'topic'      => 'required|string|max:255',
            'objectives' => 'nullable|string',
            'activities' => 'nullable|string',
            'resources'  => 'nullable|string',
            'status'     => 'required|in:draft,submitted',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);
        $data = $request->only('week_no','topic','objectives','activities','resources','status');
        if ($request->hasFile('file')) {
            if ($plan->file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($plan->file_path);
            }
            $data['file_path']          = $request->file('file')->store('lesson-plans', 'public');
            $data['file_original_name'] = $request->file('file')->getClientOriginalName();
        }
        $plan->update($data);
        return redirect()->route('teacher.lesson-plans.index')
            ->with('success', 'Lesson plan updated.');
    }
    public function destroyLessonPlan(LessonPlan $plan)
    {
        abort_if($plan->teacher_id !== auth()->id(), 403);
        abort_if($plan->status === 'reviewed', 403);
        if ($plan->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($plan->file_path);
        }
        $plan->delete();
        return back()->with('success', 'Lesson plan deleted.');
    }

    // ── Promotion Recommendations ───────────────────────────────
    public function promotionIndex()
    {
        $school = SchoolSetting::first();
        $myClassIds = $this->myClassTeacherClassIds();
        $classes = SchoolClass::whereIn('id', $myClassIds)->orderBy('numeric_order')->get();
        $year = AcademicYear::current();
        return view('teacher.promotion.index', compact('classes','year','school'));
    }

    public function promotionRoster(Request $request)
    {
        $request->validate(['class_id' => 'required|exists:classes,id']);
        $myClassIds = $this->myClassTeacherClassIds();
        if (!$myClassIds->contains((int) $request->class_id)) {
            abort(403, 'Only the class teacher may promote students from this class.');
        }
        $year = AcademicYear::current();
        $class = SchoolClass::findOrFail($request->class_id);
        $students = Student::where('class_id', $class->id)
            ->where('academic_year_id', $year->id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        // Pre-fill with any recommendations already submitted for this batch
        $existing = PromotionRecommendation::where('teacher_id', auth()->id())
            ->where('from_class_id', $class->id)
            ->where('from_academic_year_id', $year->id)
            ->get()
            ->keyBy('student_id');

        $classes = SchoolClass::orderBy('numeric_order')->get();
        $nextClass = SchoolClass::where('numeric_order', '>', $class->numeric_order)->orderBy('numeric_order')->first();
        $school = SchoolSetting::first();
        return view('teacher.promotion.roster', compact('students','class','year','existing','classes','nextClass','school'));
    }

    public function promotionStore(Request $request)
    {
        $request->validate([
            'class_id'             => 'required|exists:classes,id',
            'students'             => 'required|array|min:1',
            'students.*.id'        => 'required|exists:students,id',
            'students.*.action'    => 'required|in:promote,repeat,graduate,withdraw',
            'students.*.class_id'  => 'nullable|exists:classes,id',
            'students.*.remarks'   => 'nullable|string|max:255',
        ]);

        $myClassIds = $this->myClassTeacherClassIds();
        if (!$myClassIds->contains((int) $request->class_id)) {
            abort(403, 'Only the class teacher may promote students from this class.');
        }

        $year = AcademicYear::current();
        $class = SchoolClass::findOrFail($request->class_id);

        foreach ($request->students as $row) {
            $student = Student::findOrFail($row['id']);
            PromotionRecommendation::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'from_academic_year_id' => $year->id,
                ],
                [
                    'teacher_id'             => auth()->id(),
                    'from_class_id'          => $class->id,
                    'from_section_id'        => $student->section_id,
                    'recommended_action'     => $row['action'],
                    'recommended_class_id'   => $row['action'] === 'promote' ? ($row['class_id'] ?? null) : null,
                    'remarks'                => $row['remarks'] ?? null,
                    'status'                 => 'pending',
                ]
            );
        }

        return redirect()->route('teacher.promotion.index')
            ->with('success', 'Your promotion recommendations have been submitted to the Admin for review.');
    }

}
