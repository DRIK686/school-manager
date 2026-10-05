<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\FeePayment;
use App\Models\FeeDiscount;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeController extends Controller
{
    // Fee Categories
    public function categories()
    {
        $categories = FeeCategory::withCount('feeStructures')->get();
        return view('admin.fees.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:fee_categories']);
        FeeCategory::create($request->only('name','description'));
        return back()->with('success', 'Fee category added.');
    }

    public function destroyCategory(FeeCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    // Fee Structures
    public function structures()
    {
        $year       = AcademicYear::current();
        $structures = FeeStructure::with(['feeCategory','schoolClass','academicYear'])
            ->where('academic_year_id', $year?->id)
            ->orderBy('class_id')
            ->get();
        $categories   = FeeCategory::all();
        $classes      = SchoolClass::orderBy('numeric_order')->get();
        $academicYears= AcademicYear::orderByDesc('id')->get();
        return view('admin.fees.structures', compact('structures','categories','classes','academicYears','year'));
    }

    public function storeStructure(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'fee_category_id'  => 'required|exists:fee_categories,id',
            'amount'           => 'required|numeric|min:0',
            'due_date'         => 'nullable|date',
            'fine_per_day'     => 'nullable|numeric|min:0',
        ]);
        FeeStructure::create($request->only('academic_year_id','class_id','fee_category_id','amount','due_date','fine_per_day'));
        return back()->with('success', 'Fee structure created.');
    }

    public function destroyStructure(FeeStructure $structure)
    {
        $structure->delete();
        return back()->with('success', 'Fee structure deleted.');
    }

    // Fee Collection
    public function collect(Request $request)
    {
        $student    = null;
        $structures = collect();
        $year       = AcademicYear::current();

        if ($request->student_id) {
            $student = Student::with(['schoolClass','section'])->find($request->student_id);
            if ($student) {
                $structures = FeeStructure::with(['feeCategory','payments' => fn($q) => $q->where('student_id',$student->id)])
                    ->where('academic_year_id', $year?->id)
                    ->where(fn($q) => $q->whereNull('class_id')->orWhere('class_id',$student->class_id))
                    ->get()
                    ->map(function($s) use ($student) {
                        $paid    = $s->payments->sum('amount_paid');
                        $fine    = $s->calculateFine();
                        $balance = max(0, $s->amount - $paid);
                        $s->paid_amount   = $paid;
                        $s->fine_amount   = $fine;
                        $s->balance       = $balance;
                        $s->is_paid       = $balance <= 0;
                        return $s;
                    });
            }
        }

        $students = Student::orderBy('first_name')->get(['id','first_name','last_name','admission_no']);
        return view('admin.fees.collect', compact('student','structures','students','year'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'student_id'       => 'required|exists:students,id',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'amount_paid'      => 'required|numeric|min:0.01',
            'payment_date'     => 'required|date',
            'payment_mode'     => 'required|in:cash,bank,mobile_money,cheque',
            'reference_no'     => 'nullable|string|max:100',
        ]);

        $payment = FeePayment::create([
            'receipt_no'       => FeePayment::generateReceiptNo(),
            'student_id'       => $request->student_id,
            'academic_year_id' => AcademicYear::current()?->id,
            'fee_structure_id' => $request->fee_structure_id,
            'amount_paid'      => $request->amount_paid,
            'fine_paid'        => $request->fine_paid ?? 0,
            'discount_amount'  => $request->discount_amount ?? 0,
            'payment_date'     => $request->payment_date,
            'payment_mode'     => $request->payment_mode,
            'reference_no'     => $request->reference_no,
            'remarks'          => $request->remarks,
            'collected_by'     => auth()->id(),
        ]);

        return redirect()->route('admin.fees.receipt', $payment)
            ->with('success', "Payment recorded. Receipt: {$payment->receipt_no}");
    }

    // Receipt
    public function receipt(FeePayment $payment)
    {
        $payment->load(['student','feeStructure.feeCategory','collectedBy','academicYear']);
        $school = \App\Models\SchoolSetting::current();
        return view('admin.fees.receipt', compact('payment','school'));
    }

    public function receiptPdf(FeePayment $payment)
    {
        $payment->load(['student','feeStructure.feeCategory','collectedBy','academicYear']);
        $school = \App\Models\SchoolSetting::current();
        $pdf = Pdf::loadView('admin.fees.receipt_pdf', compact('payment','school'))
            ->setPaper('a5','portrait');
        return $pdf->download("receipt-{$payment->receipt_no}.pdf");
    }

    // Reports
    public function report(Request $request)
    {
        $year = $request->academic_year_id
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::current();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $payments = FeePayment::with(['student.schoolClass','feeStructure.feeCategory','collectedBy'])
            ->where('academic_year_id', $year?->id)
            ->when($request->date_from, fn($q) => $q->whereDate('payment_date','>=',$request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('payment_date','<=',$request->date_to))
            ->when($request->payment_mode, fn($q) => $q->where('payment_mode',$request->payment_mode))
            ->orderByDesc('payment_date')
            ->paginate(25)->withQueryString();

        $totalCollected = $payments->sum('amount_paid');
        return view('admin.fees.report', compact('payments','totalCollected','year','academicYears'));
    }

    // Balance report
    public function balanceReport(Request $request)
    {
        $year    = AcademicYear::current();
        $classId = $request->class_id;

        $students = Student::with(['schoolClass','section'])
            ->where('is_active', true)
            ->when($classId, fn($q) => $q->where('class_id',$classId))
            ->orderBy('first_name')
            ->get()
            ->map(function($student) use ($year) {
                $structures = FeeStructure::where('academic_year_id', $year?->id)
                    ->where(fn($q) => $q->whereNull('class_id')->orWhere('class_id',$student->class_id))
                    ->get();
                $totalFee  = $structures->sum('amount');
                $totalPaid = FeePayment::where('student_id',$student->id)
                    ->where('academic_year_id',$year?->id)->sum('amount_paid');
                $student->total_fee     = $totalFee;
                $student->total_paid    = $totalPaid;
                $student->total_balance = max(0, $totalFee - $totalPaid);
                return $student;
            })
            ->filter(fn($s) => $s->total_balance > 0);

        $classes = SchoolClass::orderBy('numeric_order')->get();
        return view('admin.fees.balance', compact('students','classes','year','classId'));
    }
}
