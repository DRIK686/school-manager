<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\User;
use Illuminate\Http\Request;

class ClassController extends Controller {
    public function index() {
        $classes = SchoolClass::withCount('sections')->orderBy('numeric_order')->get();
        return view('admin.academic.classes.index', compact('classes'));
    }
    public function store(Request $request) {
        $request->validate(['name' => 'required|string|max:100|unique:classes','numeric_order' => 'required|integer']);
        SchoolClass::create($request->only('name','numeric_order'));
        return back()->with('success', 'Class created.');
    }
    public function show(SchoolClass $class) {
        $sections = $class->sections()->get();
        $classSubjects = $class->classSubjects()->with(['subject','teacher'])->get();
        $allSubjects = Subject::orderBy('name')->get();
        $teachers = User::whereHas('role', fn($q) => $q->where('slug','teacher'))->orderBy('name')->get();
        return view('admin.academic.classes.show', compact('class','sections','classSubjects','allSubjects','teachers'));
    }
    public function update(Request $request, SchoolClass $class) {
        $request->validate([
            'name' => 'required|string|max:100|unique:classes,name,'.$class->id,
            'numeric_order' => 'required|integer',
            'class_teacher_id' => 'nullable|exists:users,id',
        ]);
        $class->update($request->only('name','numeric_order','class_teacher_id'));
        return back()->with('success', 'Class updated.');
    }
    public function destroy(SchoolClass $class) {
        $class->delete();
        return back()->with('success', 'Class deleted.');
    }
    public function storeSection(Request $request, SchoolClass $class) {
        $request->validate(['name' => 'required|string|max:50','capacity' => 'required|integer|min:1']);
        $class->sections()->create($request->only('name','capacity'));
        return back()->with('success', 'Section added.');
    }
    public function destroySection(SchoolClass $class, Section $section) {
        $section->delete();
        return back()->with('success', 'Section removed.');
    }
    public function assignSubject(Request $request, SchoolClass $class) {
        $request->validate(['subject_id' => 'required|exists:subjects,id','teacher_id' => 'nullable|exists:users,id']);
        ClassSubject::updateOrCreate(
            ['class_id' => $class->id, 'subject_id' => $request->subject_id],
            ['teacher_id' => $request->teacher_id]
        );
        return back()->with('success', 'Subject assigned.');
    }
    public function removeSubject(SchoolClass $class, Subject $subject) {
        ClassSubject::where('class_id',$class->id)->where('subject_id',$subject->id)->delete();
        return back()->with('success', 'Subject removed.');
    }
}
