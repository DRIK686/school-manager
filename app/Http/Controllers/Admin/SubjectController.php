<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller {
    public function index() {
        $subjects = Subject::withCount('classes')->orderBy('name')->get();
        return view('admin.academic.subjects.index', compact('subjects'));
    }
    public function store(Request $request) {
        $request->validate([
            'name'  => 'required|string|max:100',
            'code'  => 'required|string|max:20|unique:subjects',
            'type'  => 'required|in:theory,practical',
            'color' => 'required|string|max:7',
        ]);
        Subject::create($request->only('name','code','type','color'));
        return back()->with('success', 'Subject created.');
    }
    public function update(Request $request, Subject $subject) {
        $request->validate([
            'name'  => 'required|string|max:100',
            'code'  => 'required|string|max:20|unique:subjects,code,'.$subject->id,
            'type'  => 'required|in:theory,practical',
            'color' => 'required|string|max:7',
        ]);
        $subject->update($request->only('name','code','type','color'));
        return back()->with('success', 'Subject updated.');
    }
    public function destroy(Subject $subject) {
        $subject->delete();
        return back()->with('success', 'Subject deleted.');
    }
}
