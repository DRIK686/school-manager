<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\User;
use App\Models\FeePayment;
use App\Models\AcademicYear;
use App\Models\StudentAttendance;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        }

        $school = SchoolSetting::current();
        $year   = AcademicYear::current();

        $yearEndingSoon = false;
        $daysToYearEnd  = null;
        if ($year && $year->end_date) {
            $daysToYearEnd = today()->diffInDays($year->end_date, false);
            $yearEndingSoon = $daysToYearEnd <= 30;
        }


        $stats = [
            'total_students'  => Student::where('is_active', true)->count(),
            'total_teachers'  => User::whereHas('role', fn($q) => $q->where('slug','teacher'))->where('is_active',true)->count(),
            'total_staff'     => User::whereHas('role', fn($q) => $q->whereNotIn('slug',['super_admin','admin']))->where('is_active',true)->count(),
            'fees_collected'  => FeePayment::where('academic_year_id', $year?->id)->sum('amount_paid'),
            'present_today'   => StudentAttendance::whereDate('date', today())->where('status','present')->count(),
            'absent_today'    => StudentAttendance::whereDate('date', today())->where('status','absent')->count(),
            'total_classes'   => \App\Models\SchoolClass::count(),
        ];

        return view('admin.dashboard', compact('stats','school','year','yearEndingSoon','daysToYearEnd'));
    }
}
