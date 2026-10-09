<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentAttendance;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // Mark attendance page
    public function mark(Request $request)
    {
        $user = auth()->user();
        if ($user->isTeacher()) {
            $myClassIds = $user->teachingClassIds();
            $classes = SchoolClass::whereIn('id', $myClassIds)->orderBy('numeric_order')->get();
        } else {
            $classes = SchoolClass::orderBy('numeric_order')->get();
        }
        $sections = collect();
        $students = collect();
        $existing = collect();
        $date     = $request->date ?? date('Y-m-d');
        $classId  = $request->class_id;
        if ($user->isTeacher() && $classId && ! $classes->contains('id', (int) $classId)) {
            abort(403, 'You can only mark attendance for classes you teach.');
        }
        $sectionId= $request->section_id;

        if ($classId) {
            $sections = Section::where('class_id', $classId)->get();
            $query    = Student::where('class_id', $classId)->where('is_active', true)->orderBy('first_name');
            if ($sectionId) $query->where('section_id', $sectionId);
            $students = $query->get();

            $existing = StudentAttendance::where('class_id', $classId)
                ->whereDate('date', $date)
                ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
                ->get()
                ->keyBy('student_id');
        }

        $school    = \App\Models\SchoolSetting::first();
        $isTeacher = auth()->user()->isTeacher();
        $view      = $isTeacher ? 'teacher.attendance.mark' : 'admin.attendance.mark';
        return view($view, compact('classes','sections','students','existing','date','classId','sectionId','school'));
    }

    // Save attendance
    public function save(Request $request)
    {
        $request->validate([
            'class_id'       => 'required|exists:classes,id',
            'date'           => 'required|date',
            'attendance'     => 'required|array',
            'attendance.*'   => 'required|in:present,absent,late,holiday',
        ]);

        // Authorization: only the class teacher (or admin) may mark attendance.
        $user = auth()->user();
        if ($user->isTeacher() && !$user->teachesClass((int) $request->class_id)) {
            return back()->with('error', 'You can only mark attendance for classes you teach.');
        }

        $year = AcademicYear::current();

        foreach ($request->attendance as $studentId => $status) {
            StudentAttendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $request->date],
                [
                    'class_id'        => $request->class_id,
                    'section_id'      => $request->section_id,
                    'academic_year_id'=> $year?->id,
                    'status'          => $status,
                    'remarks'         => $request->remarks[$studentId] ?? null,
                    'marked_by'       => auth()->id(),
                    'taken_at'        => now(),
                ]
            );
        }

        \App\Services\ActivityLogger::log('attendance.save', 'Marked attendance for ' . count($request->attendance) . ' students in class_id ' . $request->class_id . ' on ' . $request->date . '.');
        return back()->with('success', 'Attendance saved for ' . count($request->attendance) . ' students on ' . \Carbon\Carbon::parse($request->date)->format('d M Y') . '.');
    }

    // Monthly report
    public function report(Request $request)
    {
        $user      = auth()->user();
        $isTeacher = $user->hasRole('teacher');
        // A teacher sees (and may report on) only the classes they are class teacher of, same rule as mark().
        $classes   = $isTeacher
            ? SchoolClass::whereIn('id', $user->teachingClassIds())->orderBy('numeric_order')->get()
            : SchoolClass::orderBy('numeric_order')->get();
        $classId   = $request->class_id;
        if ($isTeacher && $classId && ! $classes->contains('id', (int) $classId)) {
            abort(403, 'You can only view reports for your own class.');
        }
        $month     = $request->month ?? date('m');
        $year      = $request->year  ?? date('Y');
        $data      = collect();
        $daysInMonth = [];

        if ($classId) {
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate   = $startDate->copy()->endOfMonth();

            // Get all days in month (exclude Sundays)
            $current = $startDate->copy();
            while ($current <= $endDate) {
                if ($current->dayOfWeek !== 0) { // 0 = Sunday
                    $daysInMonth[] = $current->format('Y-m-d');
                }
                $current->addDay();
            }

            $students = Student::where('class_id', $classId)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get();

            $attendance = StudentAttendance::where('class_id', $classId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy('student_id');

            $data = $students->map(function($student) use ($attendance, $daysInMonth) {
                $records = $attendance->get($student->id, collect())->keyBy(fn($a) => $a->date->format('Y-m-d'));
                $present = $records->where('status','present')->count();
                $absent  = $records->where('status','absent')->count();
                $late    = $records->where('status','late')->count();
                $total   = count($daysInMonth);
                $pct     = $total > 0 ? round(($present + $late) / $total * 100) : 0;

                return [
                    'student'  => $student,
                    'records'  => $records,
                    'present'  => $present,
                    'absent'   => $absent,
                    'late'     => $late,
                    'total'    => $total,
                    'pct'      => $pct,
                ];
            });
        }

        $school = \App\Models\SchoolSetting::first();
        return view('admin.attendance.report', compact('classes','classId','month','year','data','daysInMonth','school'));
    }

    // Student attendance summary
    public function studentSummary(Request $request, Student $student)
    {
        $user = auth()->user();
        if ($user->hasRole('teacher')
            && ! $user->teachesClass((int) $student->class_id)) {
            abort(403, 'You can only view attendance for students in your own class.');
        }
        $year   = AcademicYear::current();
        $month  = $request->month ?? date('m');
        $yr     = $request->year  ?? date('Y');

        $records = StudentAttendance::where('student_id', $student->id)
            ->whereYear('date', $yr)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $present = $records->where('status','present')->count();
        $absent  = $records->where('status','absent')->count();
        $late    = $records->where('status','late')->count();
        $total   = $records->count();
        $pct     = $total > 0 ? round(($present + $late) / $total * 100) : 0;

        $school = \App\Models\SchoolSetting::first();
        return view('admin.attendance.student', compact('student','records','present','absent','late','total','pct','month','yr','school'));
    }
}
