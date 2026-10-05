<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountTransfer;
use App\Models\FeePayment;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\MoneyAccount;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\SchoolSetting;
use App\Models\Student;

class FinanceController extends Controller
{
    public function index()
    {
        $accounts = MoneyAccount::where('is_active', true)->orderBy('id')->get()
            ->each(fn($a) => $a->current_balance = $a->balance());
        $from = now()->startOfMonth()->toDateString();
        $to   = now()->endOfMonth()->toDateString();

        $feeIn = (float) FeePayment::whereBetween('payment_date', [$from, $to])
            ->selectRaw('COALESCE(SUM(amount_paid + fine_paid),0) as t')->value('t');
        $otherIn = (float) FinanceTransaction::active()->where('type', 'income')
            ->whereBetween('txn_date', [$from, $to])->sum('amount');
        $expense = (float) FinanceTransaction::active()->where('type', 'expense')
            ->whereBetween('txn_date', [$from, $to])->sum('amount');

        $recent = FinanceTransaction::with(['account', 'category'])
            ->orderByDesc('txn_date')->orderByDesc('id')->limit(8)->get();

        return view('admin.finance.index', [
            'accounts' => $accounts, 'totalBalance' => $accounts->sum('current_balance'),
            'pending' => FinanceTransaction::where('status', 'pending')->count(), 'feeIn' => $feeIn, 'otherIn' => $otherIn, 'expense' => $expense, 'recent' => $recent,
        ]);
    }

    // ---------- Accounts & transfers ----------
    public function accounts()
    {
        $accounts = MoneyAccount::orderBy('id')->get()->each(fn($a) => $a->current_balance = $a->balance());
        $transfers = AccountTransfer::with(['from', 'to'])->orderByDesc('transfer_date')->orderByDesc('id')->limit(10)->get();
        return view('admin.finance.accounts', compact('accounts', 'transfers'));
    }

    public function storeAccount(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:' . implode(',', array_keys(MoneyAccount::TYPES)),
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_date' => 'nullable|date',
        ]);
        $d['opening_balance'] = $d['opening_balance'] ?? 0;
        $a = MoneyAccount::create($d);
        ActivityLogger::log('finance.account', 'Created money account: ' . $a->name, $a);
        return back()->with('success', 'Account added.');
    }

    public function updateAccount(Request $r, MoneyAccount $account)
    {
        $d = $r->validate([
            'name' => 'required|string|max:100',
            'opening_balance' => 'required|numeric|min:0',
            'opening_date' => 'nullable|date',
        ]);
        $old = number_format((float) $account->opening_balance, 2);
        $account->update($d);
        ActivityLogger::log('finance.account', 'Updated account ' . $account->name . ': opening balance ' . $old . ' → ' . number_format((float) $account->opening_balance, 2), $account);
        return back()->with('success', 'Account updated.');
    }

    public function toggleAccount(MoneyAccount $account)
    {
        $account->update(['is_active' => !$account->is_active]);
        return back()->with('success', 'Account ' . ($account->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function storeTransfer(Request $r)
    {
        $d = $r->validate([
            'from_account_id' => 'required|exists:money_accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:money_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'reference_no' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:255',
        ]);
        if (self::isLocked($d['transfer_date'])) { return $this->locked(); }
        $d['created_by'] = auth()->id();
        $t = AccountTransfer::create($d);
        ActivityLogger::log('finance.transfer', 'Transfer of ' . number_format($t->amount, 2) . ' recorded.', $t);
        return back()->with('success', 'Transfer recorded.');
    }

    public function voidTransfer(Request $r, AccountTransfer $transfer)
    {
        $r->validate(['void_reason' => 'required|string|max:255']);
        if (self::isLocked($transfer->transfer_date)) { return $this->locked(); }
        if ($transfer->status !== 'void') {
            $transfer->update(['status' => 'void', 'voided_at' => now(), 'voided_by' => auth()->id(), 'void_reason' => $r->void_reason]);
            ActivityLogger::log('finance.void', 'Voided transfer #' . $transfer->id . ': ' . $r->void_reason, $transfer);
        }
        return back()->with('success', 'Transfer voided.');
    }

    // ---------- Categories ----------
    public function categories()
    {
        $expense = FinanceCategory::where('type', 'expense')->withCount('transactions')->orderBy('name')->get();
        $income  = FinanceCategory::where('type', 'income')->withCount('transactions')->orderBy('name')->get();
        return view('admin.finance.categories', compact('expense', 'income'));
    }

    public function storeCategory(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:100', 'type' => 'required|in:expense,income']);
        FinanceCategory::create($d);
        return back()->with('success', 'Category added.');
    }

    public function toggleCategory(FinanceCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        return back()->with('success', 'Category updated.');
    }

    // ---------- Expenses & other income ----------
    public function transactions(Request $r, string $type)
    {
        $base = FinanceTransaction::where('type', $type)
            ->when($r->date_from, fn($q) => $q->whereDate('txn_date', '>=', $r->date_from))
            ->when($r->date_to, fn($q) => $q->whereDate('txn_date', '<=', $r->date_to))
            ->when($r->finance_category_id, fn($q) => $q->where('finance_category_id', $r->finance_category_id))
            ->when($r->money_account_id, fn($q) => $q->where('money_account_id', $r->money_account_id));

        $total = (float) (clone $base)->where('status', 'active')->sum('amount');
        $items = $base->with(['account', 'category'])->orderByDesc('txn_date')->orderByDesc('id')
            ->paginate(25)->withQueryString();

        $categories = FinanceCategory::where('type', $type)->where('is_active', true)->orderBy('name')->get();
        $accounts = MoneyAccount::where('is_active', true)->orderBy('id')->get();
        return view('admin.finance.transactions', compact('type', 'items', 'total', 'categories', 'accounts'));
    }

    public function storeTransaction(Request $r)
    {
        $d = $r->validate([
            'type' => 'required|in:expense,income',
            'money_account_id' => 'required|exists:money_accounts,id',
            'finance_category_id' => 'required|exists:finance_categories,id',
            'txn_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payee' => 'nullable|string|max:150',
            'reference_no' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);
        $cat = FinanceCategory::findOrFail($d['finance_category_id']);
        if ($cat->type !== $d['type']) {
            return back()->withInput()->withErrors(['finance_category_id' => 'Category does not match the entry type.']);
        }
        if (self::isLocked($d['txn_date'])) { return $this->locked(); }
        if ($r->hasFile('attachment')) {
            $d['attachment_path'] = $r->file('attachment')->store('finance', 'public');
        }
        unset($d['attachment']);
        $d['created_by'] = auth()->id();
        $d['status'] = 'active';
        if ($d['type'] === 'expense') {
            $th = SchoolSetting::current()->finance_approval_threshold ?? null;
            if ($th !== null && (float) $d['amount'] >= (float) $th && !auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
                $d['status'] = 'pending';
            }
        }
        $t = FinanceTransaction::create($d);
        ActivityLogger::log('finance.' . $t->type, ucfirst($t->type) . ' of ' . number_format($t->amount, 2) . ' recorded (' . $cat->name . ').', $t);
        return back()->with('success', $t->status === 'pending' ? 'Expense saved and sent for approval.' : ucfirst($t->type) . ' recorded.');
    }

    public function voidTransaction(Request $r, FinanceTransaction $transaction)
    {
        $r->validate(['void_reason' => 'required|string|max:255']);
        if (self::isLocked($transaction->txn_date)) { return $this->locked(); }
        if ($transaction->status !== 'void') {
            $transaction->update(['status' => 'void', 'voided_at' => now(), 'voided_by' => auth()->id(), 'void_reason' => $r->void_reason]);
            ActivityLogger::log('finance.void', 'Voided ' . $transaction->type . ' #' . $transaction->id . ': ' . $r->void_reason, $transaction);
        }
        return back()->with('success', 'Entry voided.');
    }

    // ---------- Cash book ----------
    public function cashbook(Request $r)
    {
        $accounts = MoneyAccount::orderBy('id')->get();
        $account = $accounts->firstWhere('id', (int) $r->money_account_id) ?? $accounts->first();
        $from = $r->date_from ? Carbon::parse($r->date_from) : now()->startOfMonth();
        $to   = $r->date_to ? Carbon::parse($r->date_to) : now();

        $opening = $account ? $account->balance($from->copy()->subDay()->toDateString()) : 0;
        $rows = collect();
        $running = $opening;
        if ($account) {
            $rows = $account->movements($from->toDateString(), $to->toDateString())->map(function ($row) use (&$running) {
                $running = round($running + $row['in'] - $row['out'], 2);
                $row['balance'] = $running;
                return $row;
            });
        }
        return view('admin.finance.cashbook', [
            'accounts' => $accounts, 'account' => $account, 'from' => $from, 'to' => $to,
            'opening' => $opening, 'rows' => $rows, 'closing' => $running,
            'totalIn' => $rows->sum('in'), 'totalOut' => $rows->sum('out'),
        ]);
    }

    // ---------- Month / period lock ----------
    public static function lockedUntil(): ?Carbon
    {
        $v = SchoolSetting::current()->finance_locked_until ?? null;
        return $v ? Carbon::parse($v) : null;
    }

    public static function isLocked($date): bool
    {
        $l = self::lockedUntil();
        return $l && Carbon::parse($date)->lte($l);
    }

    private function locked()
    {
        return back()->withInput()->withErrors(['lock' =>
            'Books are locked up to ' . self::lockedUntil()->format('d M Y') . '. Entries on or before that date cannot be added or voided.']);
    }

    public function setLock(Request $r)
    {
        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);
        $r->validate(['locked_until' => 'nullable|date']);
        DB::table('school_settings')->update(['finance_locked_until' => $r->locked_until ?: null]);
        ActivityLogger::log('finance.lock', 'Books locked until: ' . ($r->locked_until ?: 'none (unlocked)'));
        return back()->with('success', $r->locked_until ? 'Books locked up to ' . Carbon::parse($r->locked_until)->format('d M Y') . '.' : 'Books unlocked.');
    }

    // ---------- Income & Expenditure ----------
    public function statement(Request $r)
    {
        $d = $this->statementData($r);
        if ($r->export === 'csv') {
            return $this->statementCsv($d);
        }
        if ($r->export === 'pdf') {
            $school = SchoolSetting::current();
            return Pdf::loadView('admin.finance.statement_pdf', $d + ['school' => $school])
                ->setPaper('a4', 'portrait')->download('income-expenditure.pdf');
        }
        return view('admin.finance.statement', $d);
    }

    private function byCategory(string $type, string $f, string $t)
    {
        return FinanceTransaction::active()
            ->join('finance_categories', 'finance_transactions.finance_category_id', '=', 'finance_categories.id')
            ->where('finance_transactions.type', $type)
            ->whereBetween('finance_transactions.txn_date', [$f, $t])
            ->groupBy('finance_categories.name')->orderBy('finance_categories.name')
            ->selectRaw('finance_categories.name as name, SUM(finance_transactions.amount) as amount')->get();
    }

    private function statementData(Request $r): array
    {
        $year = AcademicYear::current();
        $from = $r->date_from ? Carbon::parse($r->date_from)
            : ($year?->start_date ? Carbon::parse($year->start_date) : now()->startOfYear());
        $to = $r->date_to ? Carbon::parse($r->date_to) : now();
        $f = $from->toDateString(); $t = $to->toDateString();

        $fees = FeePayment::join('fee_structures', 'fee_payments.fee_structure_id', '=', 'fee_structures.id')
            ->join('fee_categories', 'fee_structures.fee_category_id', '=', 'fee_categories.id')
            ->whereBetween('fee_payments.payment_date', [$f, $t])
            ->groupBy('fee_categories.name')->orderBy('fee_categories.name')
            ->selectRaw('fee_categories.name as name, SUM(fee_payments.amount_paid) as amount')->get();
        $fines   = (float) FeePayment::whereBetween('payment_date', [$f, $t])->sum('fine_paid');
        $other   = $this->byCategory('income', $f, $t);
        $exp     = $this->byCategory('expense', $f, $t);

        $incomeTotal  = (float) $fees->sum('amount') + $fines + (float) $other->sum('amount');
        $expenseTotal = (float) $exp->sum('amount');

        // Fee position for the current academic year (billed vs collected vs outstanding)
        $structures = FeeStructure::with('feeCategory')->where('academic_year_id', $year?->id)->get();
        $counts = Student::where('is_active', true)->selectRaw('class_id, COUNT(*) as c')->groupBy('class_id')->pluck('c', 'class_id');
        $totalStudents = (int) $counts->sum();
        $position = [];
        foreach ($structures as $st) {
            $n = $st->class_id ? (int) ($counts[$st->class_id] ?? 0) : $totalStudents;
            $name = $st->feeCategory->name ?? 'Fees';
            $position[$name] = ['billed' => ($position[$name]['billed'] ?? 0) + (float) $st->amount * $n, 'collected' => 0, 'outstanding' => 0];
        }
        $assign = \App\Services\FeeBilling::assignments($year?->id);
        if ($assign->isNotEmpty()) {
            foreach (Student::where('is_active', true)->whereIn('id', $assign->keys())->get(['id', 'class_id']) as $stu) {
                $mine = $structures->filter(fn($x) => !$x->class_id || $x->class_id == $stu->class_id);
                foreach (\App\Services\FeeBilling::allocate($assign->get($stu->id), $mine) as $sid => $amt) {
                    $nm = optional($mine->firstWhere('id', $sid)->feeCategory)->name ?? 'Fees';
                    if (isset($position[$nm])) $position[$nm]['billed'] -= $amt;
                }
            }
        }
        $collected = FeePayment::join('fee_structures', 'fee_payments.fee_structure_id', '=', 'fee_structures.id')
            ->join('fee_categories', 'fee_structures.fee_category_id', '=', 'fee_categories.id')
            ->where('fee_payments.academic_year_id', $year?->id)
            ->groupBy('fee_categories.name')
            ->selectRaw('fee_categories.name as name, SUM(fee_payments.amount_paid) as amt')->pluck('amt', 'name');
        foreach ($collected as $name => $amt) {
            $position[$name] = ['billed' => $position[$name]['billed'] ?? 0, 'collected' => (float) $amt, 'outstanding' => 0];
        }
        foreach ($position as $name => $row) {
            $position[$name]['outstanding'] = max(0, $row['billed'] - $row['collected']);
        }

        return compact('year', 'from', 'to', 'fees', 'fines', 'other', 'exp', 'incomeTotal', 'expenseTotal', 'position') + [
            'surplus' => $incomeTotal - $expenseTotal,
        ];
    }

    private function statementCsv(array $d)
    {
        return response()->streamDownload(function () use ($d) {
            $o = fopen('php://output', 'w');
            fputcsv($o, ['Income & Expenditure', $d['from']->format('Y-m-d') . ' to ' . $d['to']->format('Y-m-d')]);
            fputcsv($o, []);
            fputcsv($o, ['INCOME', '']);
            foreach ($d['fees'] as $r) fputcsv($o, ['Fees: ' . $r->name, number_format((float) $r->amount, 2, '.', '')]);
            if ($d['fines'] > 0) fputcsv($o, ['Fines & penalties', number_format($d['fines'], 2, '.', '')]);
            foreach ($d['other'] as $r) fputcsv($o, [$r->name, number_format((float) $r->amount, 2, '.', '')]);
            fputcsv($o, ['Total income', number_format($d['incomeTotal'], 2, '.', '')]);
            fputcsv($o, []);
            fputcsv($o, ['EXPENDITURE', '']);
            foreach ($d['exp'] as $r) fputcsv($o, [$r->name, number_format((float) $r->amount, 2, '.', '')]);
            fputcsv($o, ['Total expenditure', number_format($d['expenseTotal'], 2, '.', '')]);
            fputcsv($o, []);
            fputcsv($o, ['Surplus / (Deficit)', number_format($d['surplus'], 2, '.', '')]);
            fputcsv($o, []);
            fputcsv($o, ['FEE POSITION (current academic year)', 'Billed', 'Collected', 'Outstanding']);
            foreach ($d['position'] as $name => $r) fputcsv($o, [$name, $r['billed'], $r['collected'], $r['outstanding']]);
            fclose($o);
        }, 'income-expenditure.csv', ['Content-Type' => 'text/csv']);
    }

    // ---------- Approval ----------
    public function approve(FinanceTransaction $transaction)
    {
        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);
        if ($transaction->status !== 'pending') return back();
        if (self::isLocked($transaction->txn_date)) return $this->locked();
        $transaction->update(['status' => 'active']);
        ActivityLogger::log('finance.approve', 'Approved expense #' . $transaction->id . ' of ' . number_format($transaction->amount, 2) . '.', $transaction);
        return back()->with('success', 'Expense approved.');
    }

    public function reject(Request $r, FinanceTransaction $transaction)
    {
        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);
        $r->validate(['void_reason' => 'required|string|max:255']);
        if ($transaction->status !== 'pending') return back();
        $transaction->update(['status' => 'rejected', 'voided_at' => now(), 'voided_by' => auth()->id(), 'void_reason' => $r->void_reason]);
        ActivityLogger::log('finance.reject', 'Rejected expense #' . $transaction->id . ': ' . $r->void_reason, $transaction);
        return back()->with('success', 'Expense rejected.');
    }

    public function setThreshold(Request $r)
    {
        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);
        $r->validate(['threshold' => 'nullable|numeric|min:0']);
        $v = ($r->threshold === null || $r->threshold === '') ? null : $r->threshold;
        DB::table('school_settings')->update(['finance_approval_threshold' => $v]);
        ActivityLogger::log('finance.approval', 'Expense approval threshold set to: ' . ($v ?? 'off'));
        return back()->with('success', $v === null ? 'Approval turned off.' : 'Approval required for expenses of ' . number_format((float) $v, 2) . ' and above (entered by non-admins).');
    }

    // ---------- Voucher ----------
    public function voucher(FinanceTransaction $transaction)
    {
        abort_unless($transaction->status === 'active' && $transaction->type === 'expense', 404);
        $transaction->load(['account', 'category', 'creator']);
        $school = SchoolSetting::current();
        return Pdf::loadView('admin.finance.voucher', compact('transaction', 'school'))
            ->setPaper('a5', 'portrait')
            ->download('voucher-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }

    // ---------- Budget ----------
    public function budget(Request $r)
    {
        $years = AcademicYear::orderByDesc('start_date')->get();
        $year = $r->academic_year_id ? $years->firstWhere('id', (int) $r->academic_year_id) : AcademicYear::current();
        $year = $year ?? $years->first();
        $from = $year?->start_date ? Carbon::parse($year->start_date) : now()->startOfYear();
        $to   = ($year?->end_date ?? null) ? Carbon::parse($year->end_date) : now()->endOfYear();

        $categories = FinanceCategory::where('type', 'expense')->where('is_active', true)->orderBy('name')->get();
        $budgets = DB::table('finance_budgets')->where('academic_year_id', $year?->id)->pluck('amount', 'finance_category_id');
        $actual = FinanceTransaction::active()->where('type', 'expense')
            ->whereBetween('txn_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('finance_category_id, SUM(amount) as a')->groupBy('finance_category_id')->pluck('a', 'finance_category_id');

        return view('admin.finance.budget', compact('years', 'year', 'categories', 'budgets', 'actual', 'from', 'to'));
    }

    public function saveBudget(Request $r)
    {
        $r->validate(['academic_year_id' => 'required|exists:academic_years,id', 'amounts' => 'nullable|array', 'amounts.*' => 'nullable|numeric|min:0']);
        foreach ((array) $r->amounts as $catId => $amt) {
            $key = ['academic_year_id' => $r->academic_year_id, 'finance_category_id' => (int) $catId];
            if ($amt === null || $amt === '') {
                DB::table('finance_budgets')->where($key)->delete();
                continue;
            }
            DB::table('finance_budgets')->updateOrInsert($key, ['amount' => $amt, 'updated_at' => now()]);
        }
        ActivityLogger::log('finance.budget', 'Budget saved for academic year #' . $r->academic_year_id . '.');
        return back()->with('success', 'Budget saved.');
    }
}
