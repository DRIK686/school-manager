<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PromotionRecommendation;

class PromotionController extends Controller
{
    // Step 1: pick source year/class/section to promote FROM
    public function index()
    {
        $user = auth()->user();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $classesQuery = SchoolClass::orderBy('numeric_order');
        if ($user->isTeacher()) {
            $classesQuery->where('class_teacher_id', $user->id);
        }
        $classes = $classesQuery->get();
        $pendingRecommendations = PromotionRecommendation::where('status','pending')
            ->when($user->isTeacher(), fn($q) => $q->whereIn('from_class_id', $classes->pluck('id')))
            ->with(['teacher','fromClass'])
            ->get()
            ->groupBy('from_class_id');
        return view('admin.promotion.index', compact('academicYears','classes','pendingRecommendations'));
    }

    public function recommendations(Request $request)
    {
        $classId = $request->class_id;
        $recs = PromotionRecommendation::where('status','pending')
            ->when($classId, fn($q) => $q->where('from_class_id', $classId))
            ->with(['student','teacher','fromClass','recommendedClass'])
            ->get();
        return view('admin.promotion.recommendations', compact('recs','classId'));
    }

    // Step 2: show roster for the chosen source, with a suggested target class/section per student
    public function roster(Request $request)
    {
        $request->validate([
            'from_academic_year_id' => 'required|exists:academic_years,id',
            'from_class_id'         => 'required|exists:classes,id',
            'from_section_id'       => 'nullable|exists:sections,id',
        ]);

        // Authorization: only the class teacher (or admin) may promote this class.
        $user = auth()->user();
        if ($user->isTeacher()) {
            abort_unless($user->isClassTeacherOf($request->from_class_id), 403, 'Only the class teacher may promote students from this class.');
        }

        $students = Student::with(['schoolClass','section'])
            ->where('academic_year_id', $request->from_academic_year_id)
            ->where('class_id', $request->from_class_id)
            ->when($request->from_section_id, fn($q) => $q->where('section_id', $request->from_section_id))
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $fromClass = SchoolClass::findOrFail($request->from_class_id);
        $nextClass = SchoolClass::where('numeric_order', '>', $fromClass->numeric_order)
            ->orderBy('numeric_order')
            ->first();

        $classes = SchoolClass::orderBy('numeric_order')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $sections = Section::where('class_id', $nextClass->id ?? null)->get();

        $recommendations = PromotionRecommendation::where('from_class_id', $request->from_class_id)
            ->where('from_academic_year_id', $request->from_academic_year_id)
            ->get()
            ->keyBy('student_id');

        return view('admin.promotion.roster', compact(
            'students','fromClass','nextClass','classes','sections','academicYears','request','recommendations'
        ));
    }

    // Step 3: execute the promotion
    public function process(Request $request)
    {
        $request->validate([
            'to_academic_year_id' => 'required|exists:academic_years,id',
            'students'            => 'required|array|min:1',
            'students.*.id'       => 'required|exists:students,id',
            'students.*.action'   => 'required|in:promote,repeat,graduate,withdraw',
            'students.*.class_id'    => 'nullable|exists:classes,id',
            'students.*.section_id'  => 'nullable|exists:sections,id',
        ]);

        // Authorization: only the class teacher (or admin) may promote students.
        // Check the CURRENT class of every affected student before anything changes.
        $user = auth()->user();
        if ($user->isTeacher()) {
            $affectedClassIds = Student::whereIn('id', collect($request->students)->pluck('id'))
                ->pluck('class_id')->unique();
            foreach ($affectedClassIds as $classId) {
                abort_unless($user->isClassTeacherOf($classId), 403, 'Only the class teacher may promote students from this class.');
            }
        }

        $toYearId = $request->to_academic_year_id;
        $count = ['promoted' => 0, 'repeated' => 0, 'graduated' => 0, 'withdrawn' => 0];

        DB::transaction(function () use ($request, $toYearId, &$count) {
            foreach ($request->students as $row) {
                $student = Student::findOrFail($row['id']);

                // 1. Snapshot the OLD placement into history before changing anything
                StudentEnrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year_id' => $student->academic_year_id,
                    ],
                    [
                        'class_id'   => $student->class_id,
                        'section_id' => $student->section_id,
                        'roll_no'    => $student->roll_no,
                        'status'     => $row['action'] === 'promote' ? 'promoted'
                                      : ($row['action'] === 'repeat' ? 'repeated'
                                      : ($row['action'] === 'graduate' ? 'graduated' : 'withdrawn')),
                    ]
                );

                // 1b. Mark any teacher recommendation for this student as actioned
                PromotionRecommendation::where('student_id', $student->id)
                    ->where('from_academic_year_id', $student->academic_year_id)
                    ->update(['status' => 'actioned']);

                // 2. Apply the change to the LIVE record
                if ($row['action'] === 'promote') {
                    $student->update([
                        'class_id'         => $row['class_id'],
                        'section_id'       => $row['section_id'] ?? null,
                        'academic_year_id' => $toYearId,
                    ]);
                    $count['promoted']++;
                } elseif ($row['action'] === 'repeat') {
                    $student->update([
                        // class/section stay the same, only the year moves forward
                        'academic_year_id' => $toYearId,
                    ]);
                    $count['repeated']++;
                } elseif ($row['action'] === 'graduate') {
                    $student->update([
                        'is_active' => false,
                    ]);
                    $count['graduated']++;
                } elseif ($row['action'] === 'withdraw') {
                    $student->update([
                        'is_active' => false,
                    ]);
                    $count['withdrawn']++;
                }
            }
        });

        return redirect()->route('admin.promotion.index')->with('success',
            "Promotion complete — Promoted: {$count['promoted']}, Repeated: {$count['repeated']}, Graduated: {$count['graduated']}, Withdrawn: {$count['withdrawn']}."
        );
    }

    // View a student's full year-by-year history
    public function history(Student $student)
    {
        $enrollments = $student->enrollments()->with(['academicYear','schoolClass','section'])
            ->orderByDesc('academic_year_id')->get();
        return view('admin.promotion.history', compact('student','enrollments'));
    }
}
