<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller {
    public function index() {
        $years = AcademicYear::latest()->get();
        return view('admin.academic.years.index', compact('years'));
    }
    public function store(Request $request) {
        $request->validate([
            'name'       => 'required|string|max:20|unique:academic_years',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);
        AcademicYear::create($request->only('name','start_date','end_date'));
        return back()->with('success', 'Academic year created.');
    }
    public function setCurrent(AcademicYear $year) {
        $year->setCurrent();
        return back()->with('success', "\"{$year->name}\" is now the current academic year.");
    }
    public function destroy(AcademicYear $year) {
        if ($year->is_current) return back()->with('error', 'Cannot delete the current academic year.');
        $year->delete();
        return back()->with('success', 'Academic year deleted.');
    }
}
