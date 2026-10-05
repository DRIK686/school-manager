<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    private function classesForUser()
    {
        $user = auth()->user();
        if ($user->isTeacher()) {
            $ids = ClassSubject::where('teacher_id', $user->id)->pluck('class_id')->unique();
            return SchoolClass::whereIn('id', $ids)->orderBy('numeric_order')->get();
        }
        return SchoolClass::orderBy('numeric_order')->get();
    }

    public function index(Request $request)
    {
        $school   = SchoolSetting::first();
        $year     = AcademicYear::current();
        $classes  = $this->classesForUser();
        $classId  = $request->class_id  ?? $classes->first()?->id;
        $sectionId= $request->section_id ?? null;

        $slots    = TimetableSlot::orderBy('slot_no')->get();
        $days     = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
        $sections = $classId ? Section::where('class_id', $classId)->get() : collect();

        // Build grid: day -> slot_id -> timetable entry
        $grid = [];
        if ($classId && $year) {
            $entries = Timetable::where('academic_year_id', $year->id)
                ->where('class_id', $classId)
                ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
                ->with(['subject','teacher','slot'])
                ->get();

            foreach ($days as $dayNo => $dayName) {
                foreach ($slots as $slot) {
                    $entry = $entries->first(fn($e) => $e->day_of_week == $dayNo && $e->slot_id == $slot->id);
                    $grid[$dayNo][$slot->id] = $entry;
                }
            }
        }

        $subjects = Subject::orderBy('name')->get();
        $teachers = User::whereHas('role', fn($q) => $q->where('slug','teacher'))->orderBy('name')->get();
        $isTeacher = auth()->user()->isTeacher();

        $view = $isTeacher ? 'teacher.timetable.index' : 'admin.timetable.index';
        return view($view, compact(
            'school','year','classes','classId','sectionId','sections',
            'slots','days','grid','subjects','teachers','isTeacher'
        ));
    }

    public function save(Request $request)
    {
        $request->validate([
            'class_id'         => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $yearId    = $request->academic_year_id;
        $classId   = $request->class_id;
        $sectionId = $request->section_id ?: null;

        // entries is array: day -> slot_id -> [subject_id, teacher_id, custom_label]
        foreach ($request->entries ?? [] as $dayNo => $slots) {
            foreach ($slots as $slotId => $data) {
                $subjectId   = $data['subject_id']   ?? null;
                $teacherId   = $data['teacher_id']   ?? null;
                $customLabel = $data['custom_label'] ?? null;

                if ($subjectId || $customLabel) {
                    Timetable::updateOrCreate(
                        [
                            'academic_year_id' => $yearId,
                            'class_id'         => $classId,
                            'section_id'       => $sectionId,
                            'day_of_week'      => $dayNo,
                            'slot_id'          => $slotId,
                        ],
                        [
                            'subject_id'   => $subjectId ?: null,
                            'teacher_id'   => $teacherId ?: null,
                            'custom_label' => $customLabel ?: null,
                        ]
                    );
                } else {
                    // Clear the cell if both empty
                    Timetable::where([
                        'academic_year_id' => $yearId,
                        'class_id'         => $classId,
                        'section_id'       => $sectionId,
                        'day_of_week'      => $dayNo,
                        'slot_id'          => $slotId,
                    ])->delete();
                }
            }
        }

        return back()->with('success', 'Timetable saved successfully.');
    }

    // Read-only printable view
    public function view(Request $request)
    {
        $school    = SchoolSetting::first();
        $year      = AcademicYear::current();
        $classes   = $this->classesForUser();
        $classId   = $request->class_id ?? $classes->first()?->id;
        $sectionId = $request->section_id ?? null;
        $slots     = TimetableSlot::orderBy('slot_no')->get();
        $days      = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
        $sections  = $classId ? Section::where('class_id', $classId)->get() : collect();
        $grid      = [];

        if ($classId && $year) {
            $entries = Timetable::where('academic_year_id', $year->id)
                ->where('class_id', $classId)
                ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
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

        $selectedClass = $classId ? SchoolClass::find($classId) : null;
        $isTeacher     = auth()->user()->isTeacher();
        $view = $isTeacher ? 'teacher.timetable.view' : 'admin.timetable.view';
        return view($view, compact(
            'school','year','classes','classId','sectionId','sections',
            'slots','days','grid','selectedClass','isTeacher'
        ));
    }
}
