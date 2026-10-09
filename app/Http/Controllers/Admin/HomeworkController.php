<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class HomeworkController extends Controller
{
    public function index(Request $request)
    {
        $school = SchoolSetting::first();
        $user   = auth()->user();

        $query = Homework::withCount('submissions')->with(['schoolClass','subject','teacher'])
            ->when($user->isTeacher(), fn($q) => $q->where('teacher_id', $user->id))
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->orderByDesc('due_date');

        $homework = $query->get();
        $classes  = $user->isTeacher()
            ? SchoolClass::whereIn('id', ClassSubject::where('teacher_id', $user->id)->pluck('class_id'))->get()
            : SchoolClass::orderBy('numeric_order')->get();

        $isTeacher = $user->isTeacher();
        $view      = $isTeacher ? 'teacher.homework.index' : 'admin.homework.index';
        return view($view, compact('school','homework','classes','isTeacher'));
    }

    public function create()
    {
        $school     = SchoolSetting::first();
        $user       = auth()->user();
        $isTeacher  = $user->isTeacher();
        $classes    = $isTeacher
            ? SchoolClass::whereIn('id', ClassSubject::where('teacher_id', $user->id)->pluck('class_id'))->orderBy('numeric_order')->get()
            : SchoolClass::orderBy('numeric_order')->get();
        $subjects   = Subject::orderBy('name')->get();

        // Subjects actually assigned to each class, for the dependent subject dropdown.
        $classSubjectsQuery = $isTeacher ? ClassSubject::where('teacher_id', $user->id) : ClassSubject::query();
        $classSubjectsMap = $classSubjectsQuery->with('subject:id,name')->get()
            ->groupBy('class_id')
            ->map(fn($group) => $group->map(fn($cs) => ['id' => $cs->subject_id, 'name' => $cs->subject->name])->values());

        $view       = $isTeacher ? 'teacher.homework.create' : 'admin.homework.create';
        return view($view, compact('school','classes','subjects','isTeacher','classSubjectsMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title'      => 'required|string|max:255',
            'due_date'   => 'required|date',
            'description'=> 'nullable|string',
        ]);

        $subjectAssigned = ClassSubject::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->when(auth()->user()->isTeacher(), fn($q) => $q->where('teacher_id', auth()->id()))
            ->exists();
        if (!$subjectAssigned) {
            return back()->withErrors(['subject_id' => (auth()->user()->isTeacher() ? 'You are not assigned to teach that subject in the selected class.' : 'That subject is not assigned to the selected class.')])->withInput();
        }

        Homework::create([
            'class_id'        => $request->class_id,
            'subject_id'      => $request->subject_id,
            'teacher_id'      => auth()->id(),
            'academic_year_id'=> AcademicYear::current()?->id,
            'title'           => $request->title,
            'description'     => $request->description,
            'due_date'        => $request->due_date,
        ]);

        $route = auth()->user()->isTeacher() ? 'teacher.homework.index' : 'admin.homework.index';
        return redirect()->route($route)->with('success', 'Homework posted.');
    }

    public function destroy(Homework $homework)
    {
        $user = auth()->user();
        if ($user->isTeacher() && $homework->teacher_id !== $user->id) abort(403);
        $homework->delete();
        return back()->with('success', 'Homework deleted.');
    }

    public function submissions(Homework $homework)
    {
        $school      = SchoolSetting::first();
        $user        = auth()->user();
        if ($user->isTeacher() && $homework->teacher_id !== $user->id) abort(403);
        $submissions = HomeworkSubmission::where('homework_id', $homework->id)
            ->with(['student.schoolClass','gradedBy'])
            ->orderBy('submitted_at')->get();
        $view = $user->isTeacher() ? 'teacher.homework.submissions' : 'admin.homework.submissions';
        return view($view, compact('school','homework','submissions'));
    }

    public function grade(\Illuminate\Http\Request $request, Homework $homework, HomeworkSubmission $submission)
    {
        // Authorization: a teacher may only grade submissions for homework
        // they themselves set. Admins can grade anything.
        $user = auth()->user();
        if ($user->isTeacher()) {
            abort_unless($homework->teacher_id === $user->id, 403, 'You did not set this homework.');
        }
        abort_unless($submission->homework_id === $homework->id, 404);

        $request->validate([
            'score'    => 'nullable|numeric|min:0|max:100',
            'feedback' => 'required|string',
        ]);
        $submission->update([
            'score'      => $request->score,
            'feedback'   => $request->feedback,
            'status'     => 'graded',
            'graded_by'  => auth()->id(),
            'graded_at'  => now(),
        ]);
        \App\Services\ActivityLogger::log('homework.grade', 'Graded homework submission for ' . ($submission->student->full_name ?? 'student #'.$submission->student_id) . ' — score: ' . ($request->score ?? 'n/a'), $submission);
        return back()->with('success', 'Submission graded.');
    }

}
