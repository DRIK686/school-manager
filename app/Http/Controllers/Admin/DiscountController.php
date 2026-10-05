<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiscountController extends Controller
{
    private function stamp(string $table, array $row): array
    {
        if (Schema::hasColumn($table, 'created_at')) $row['created_at'] = now();
        if (Schema::hasColumn($table, 'updated_at')) $row['updated_at'] = now();
        return $row;
    }

    public function index()
    {
        $year = AcademicYear::current();
        $discounts = DB::table('fee_discounts')->orderBy('name')->get();
        $counts = DB::table('student_fee_discounts')->selectRaw('fee_discount_id, COUNT(*) as c')
            ->groupBy('fee_discount_id')->pluck('c', 'fee_discount_id');
        $assigned = DB::table('student_fee_discounts as sd')
            ->join('students as s', 'sd.student_id', '=', 's.id')
            ->join('fee_discounts as d', 'sd.fee_discount_id', '=', 'd.id')
            ->where('sd.academic_year_id', $year?->id)->whereNull('s.deleted_at')
            ->orderBy('s.first_name')
            ->get(['sd.id', 's.first_name', 's.last_name', 's.admission_no', 'd.name', 'd.discount_type', 'd.value']);
        $students = Student::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'admission_no']);
        return view('admin.fees.discounts', compact('year', 'discounts', 'counts', 'assigned', 'students'));
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|max:100',
            'discount_type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
        ]);
        if ($d['discount_type'] === 'percent' && $d['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'A percentage cannot exceed 100.']);
        }
        DB::table('fee_discounts')->insert($this->stamp('fee_discounts', $d));
        ActivityLogger::log('fees.discount', 'Created discount type: ' . $d['name']);
        return back()->with('success', 'Discount type added.');
    }

    public function destroy(int $id)
    {
        if (DB::table('student_fee_discounts')->where('fee_discount_id', $id)->exists()) {
            return back()->withErrors(['discount' => 'This discount is assigned to students. Remove those first.']);
        }
        DB::table('fee_discounts')->where('id', $id)->delete();
        return back()->with('success', 'Discount type deleted.');
    }

    public function assign(Request $r)
    {
        $d = $r->validate([
            'student_id' => 'required|exists:students,id',
            'fee_discount_id' => 'required|exists:fee_discounts,id',
        ]);
        $year = AcademicYear::current();
        if (!$year) {
            return back()->withErrors(['discount' => 'No current academic year is set.']);
        }
        $row = ['student_id' => $d['student_id'], 'fee_discount_id' => $d['fee_discount_id'], 'academic_year_id' => $year->id];
        if (DB::table('student_fee_discounts')->where($row)->exists()) {
            return back()->withErrors(['discount' => 'That student already has this discount.']);
        }
        DB::table('student_fee_discounts')->insert($this->stamp('student_fee_discounts', $row));
        ActivityLogger::log('fees.discount', 'Assigned discount #' . $d['fee_discount_id'] . ' to student #' . $d['student_id'] . '.');
        return back()->with('success', 'Discount assigned for ' . $year->name . '.');
    }

    public function unassign(int $id)
    {
        DB::table('student_fee_discounts')->where('id', $id)->delete();
        ActivityLogger::log('fees.discount', 'Removed student discount assignment #' . $id . '.');
        return back()->with('success', 'Discount removed.');
    }
}
