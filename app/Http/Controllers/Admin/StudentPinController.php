<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Mail\StudentPinMail;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class StudentPinController extends Controller
{
    public function index(Request $request)
    {
        $school   = SchoolSetting::first();
        $classId  = $request->class_id;
        $students = Student::where('is_active', true)
            ->with(['pin','schoolClass'])
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->orderBy('first_name')->get();
        $classes  = SchoolClass::orderBy('numeric_order')->get();
        return view('admin.students.pins', compact('school','students','classes','classId'));
    }

    public function reset(Student $student)
    {
        $plain  = $student->generatePin();
        $parent = StudentParent::where('student_id', $student->id)
            ->whereNotNull('email')->first();
        $sent   = false;

        if ($parent) {
            try {
                MailConfigService::applyFromSettings();
                $student->load('schoolClass');
                Mail::to($parent->email)->send(new StudentPinMail($student, $plain, $parent));
                $sent = true;
            } catch (\Exception $e) {
                Log::error('PIN email failed: ' . $e->getMessage());
            }
        }

        $msg = "PIN reset for {$student->full_name}. New PIN: {$plain}";
        if ($sent)        $msg .= " — Email sent to {$parent->email}";
        elseif ($parent)  $msg .= " — Email failed, check SMTP settings";
        else              $msg .= " — No parent email on file";

        return back()->with('success', $msg);
    }

    public function resetAll(Request $request)
    {
        $students = Student::where('is_active', true)
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->with('schoolClass')
            ->get();

        MailConfigService::applyFromSettings();
        $emailed = 0;

        // Pre-load all parent emails for these students
        $parentEmails = StudentParent::whereIn('student_id', $students->pluck('id'))
            ->whereNotNull('email')
            ->get()->keyBy('student_id');

        foreach ($students as $student) {
            $plain  = $student->generatePin();
            $parent = $parentEmails->get($student->id);
            if ($parent) {
                try {
                    $student->load('schoolClass');
                    Mail::to($parent->email)->send(new StudentPinMail($student, $plain, $parent));
                    $emailed++;
                } catch (\Exception $e) {
                    Log::error('PIN email failed for ' . $student->full_name . ': ' . $e->getMessage());
                }
            }
        }

        return back()->with('success', "PINs regenerated for {$students->count()} students. {$emailed} email(s) sent.");
    }
}
