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
use Illuminate\Support\Facades\DB;
use App\Models\MoneyAccount;
use App\Services\ActivityLogger;

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
                    ->get();
                $discounts = \App\Services\FeeBilling::discounts($student->id, $year?->id, $structures);
                $structures = $structures
                    ->map(function($s) use ($student, $discounts) {
                        $paid    = $s->payments->sum('amount_paid');
                        $fine    = max(0, $s->calculateFine() - (float) $s->payments->sum('fine_paid'));
                        $s->discount_applied = (float) ($discounts[$s->id] ?? 0);
                        $balance = max(0, $s->amount - $s->discount_applied - $paid);
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
        $data = $request->validate([
            'student_id'       => 'required|exists:students,id',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'amount_paid'      => 'required|numeric|min:0.01',
            'fine_paid'        => 'nullable|numeric|min:0',
            'discount_amount'  => 'nullable|numeric|min:0',
            'payment_date'     => 'required|date',
            'payment_mode'     => 'required|in:cash,bank,mobile_money,cheque',
            'money_account_id' => 'nullable|exists:money_accounts,id',
            'reference_no'     => 'nullable|string|max:100',
            'remarks'          => 'nullable|string|max:500',
        ]);

        if (FinanceController::isLocked($data['payment_date'])) {
            return back()->withInput()->withErrors(['lock' => 'Books are locked up to ' . FinanceController::lockedUntil()->format('d M Y') . '. Payments dated on or before that cannot be added.']);
        }
        $structure = FeeStructure::findOrFail($data['fee_structure_id']);
        $balance = $structure->balanceForStudent((int) $data['student_id']);
        if ((float) $data['amount_paid'] > $balance + 0.005) {
            return back()->withInput()->withErrors([
                'amount_paid' => 'Amount exceeds the outstanding balance of ' . number_format($balance, 2) . ' for this fee.',
            ]);
        }

        $accountId = $data['money_account_id'] ?? MoneyAccount::forMode($data['payment_mode'])?->id;

        $payment = DB::transaction(function () use ($data, $request, $accountId) {
            return FeePayment::create([
                'receipt_no'       => FeePayment::generateReceiptNo(),
                'student_id'       => $data['student_id'],
                'academic_year_id' => AcademicYear::current()?->id,
                'fee_structure_id' => $data['fee_structure_id'],
                'amount_paid'      => $data['amount_paid'],
                'fine_paid'        => $data['fine_paid'] ?? 0,
                'discount_amount'  => $data['discount_amount'] ?? 0,
                'payment_date'     => $data['payment_date'],
                'payment_mode'     => $data['payment_mode'],
                'money_account_id' => $accountId,
                'reference_no'     => $data['reference_no'] ?? null,
                'remarks'          => $data['remarks'] ?? null,
                'collected_by'     => auth()->id(),
            ]);
        });

        ActivityLogger::log('fees.payment', "Fee payment {$payment->receipt_no} of " . number_format($payment->amount_paid, 2) . ' recorded.', $payment);

        return redirect()->route('admin.fees.receipt', $payment)
            ->with('success', "Payment recorded. Receipt: {$payment->receipt_no}");
    }

    public function voidPayment(Request $request, FeePayment $payment)
    {
        $request->validate(['void_reason' => 'required|string|max:255']);
        if (FinanceController::isLocked($payment->payment_date)) {
            return back()->withErrors(['lock' => 'Books are locked up to ' . FinanceController::lockedUntil()->format('d M Y') . '. This payment cannot be voided.']);
        }
        $payment->forceFill([
            'status' => 'void', 'voided_at' => now(),
            'voided_by' => auth()->id(), 'void_reason' => $request->void_reason,
        ])->save();
        ActivityLogger::log('fees.void', "Voided receipt {$payment->receipt_no}: {$request->void_reason}", $payment);
        return back()->with('success', "Receipt {$payment->receipt_no} voided.");
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

        $totalCollected = FeePayment::where('academic_year_id', $year?->id)
            ->when($request->date_from, fn($q) => $q->whereDate('payment_date','>=',$request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('payment_date','<=',$request->date_to))
            ->when($request->payment_mode, fn($q) => $q->where('payment_mode',$request->payment_mode))
            ->sum('amount_paid');
        return view('admin.fees.report', compact('payments','totalCollected','year','academicYears'));
    }

    // Balance report (outstanding fees by student, after discounts)
    public function balanceReport(Request $request)
    {
        $year    = AcademicYear::current();
        $classId = $request->class_id;

        $structures = FeeStructure::where('academic_year_id', $year?->id)->get();
        $assign     = \App\Services\FeeBilling::assignments($year?->id);
        $paid       = FeePayment::where('academic_year_id', $year?->id)
            ->selectRaw('student_id, SUM(amount_paid) as t')->groupBy('student_id')->pluck('t', 'student_id');

        $students = Student::with(['schoolClass','section'])
            ->where('is_active', true)
            ->when($classId, fn($q) => $q->where('class_id',$classId))
            ->orderBy('first_name')
            ->get()
            ->map(function($student) use ($structures, $assign, $paid) {
                $mine      = $structures->filter(fn($s) => !$s->class_id || $s->class_id == $student->class_id);
                $discount  = array_sum(\App\Services\FeeBilling::allocate($assign->get($student->id, collect()), $mine));
                $totalFee  = (float) $mine->sum('amount') - $discount;
                $totalPaid = (float) ($paid[$student->id] ?? 0);
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
