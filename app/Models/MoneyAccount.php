<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MoneyAccount extends Model {
    const TYPES = ['cash' => 'Cash', 'bank' => 'Bank', 'mobile_money' => 'Mobile Money'];

    protected $fillable = ['name', 'type', 'opening_balance', 'opening_date', 'is_active'];
    protected $casts = ['opening_balance' => 'decimal:2', 'opening_date' => 'date', 'is_active' => 'boolean'];

    /** Default account for a fee payment mode (cash->cash, bank/cheque->bank, mobile_money->mobile_money). */
    public static function forMode(string $mode): ?self
    {
        $type = in_array($mode, ['bank', 'cheque']) ? 'bank' : $mode;
        return static::where('type', $type)->where('is_active', true)->orderBy('id')->first();
    }

    /** All money movements (in/out) for this account, oldest first. */
    public function movements($from = null, $to = null): Collection
    {
        $rows = collect();

        $payments = FeePayment::with(['student', 'feeStructure.feeCategory'])
            ->where('money_account_id', $this->id)
            ->when($from, fn($q) => $q->whereDate('payment_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('payment_date', '<=', $to))
            ->get();
        foreach ($payments as $p) {
            $rows->push([
                'date' => $p->payment_date, 'kind' => 'Fees', 'ref' => $p->receipt_no,
                'desc' => ($p->student->full_name ?? 'Student') . ' — ' . ($p->feeStructure->feeCategory->name ?? 'Fees'),
                'in' => (float) $p->amount_paid + (float) $p->fine_paid, 'out' => 0.0, 'sort' => 1,
                'void_url' => route('admin.fees.void', $p),
            ]);
        }

        $txns = FinanceTransaction::active()->with('category')
            ->where('money_account_id', $this->id)
            ->when($from, fn($q) => $q->whereDate('txn_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('txn_date', '<=', $to))
            ->get();
        foreach ($txns as $t) {
            $isIn = $t->type === 'income';
            $rows->push([
                'date' => $t->txn_date, 'kind' => $isIn ? 'Income' : 'Expense', 'ref' => $t->reference_no,
                'desc' => ($t->category->name ?? '') . ($t->payee ? ' — ' . $t->payee : ''),
                'in' => $isIn ? (float) $t->amount : 0.0, 'out' => $isIn ? 0.0 : (float) $t->amount, 'sort' => 2,
                'void_url' => route('admin.finance.transactions.void', $t),
            ]);
        }

        $transfers = AccountTransfer::active()->with(['from', 'to'])
            ->where(fn($q) => $q->where('from_account_id', $this->id)->orWhere('to_account_id', $this->id))
            ->when($from, fn($q) => $q->whereDate('transfer_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('transfer_date', '<=', $to))
            ->get();
        foreach ($transfers as $tr) {
            $out = $tr->from_account_id === $this->id;
            $rows->push([
                'date' => $tr->transfer_date, 'kind' => 'Transfer', 'ref' => $tr->reference_no,
                'desc' => $out ? 'Transfer to ' . ($tr->to->name ?? '') : 'Transfer from ' . ($tr->from->name ?? ''),
                'in' => $out ? 0.0 : (float) $tr->amount, 'out' => $out ? (float) $tr->amount : 0.0, 'sort' => 3,
                'void_url' => route('admin.finance.transfers.void', $tr),
            ]);
        }

        return $rows->sortBy(fn($r) => $r['date']->format('Y-m-d') . $r['sort'])->values();
    }

    /** Balance including everything up to and including $upTo (null = today and beyond). */
    public function balance($upTo = null): float
    {
        $m = $this->movements(null, $upTo);
        return round((float) $this->opening_balance + $m->sum('in') - $m->sum('out'), 2);
    }
}
