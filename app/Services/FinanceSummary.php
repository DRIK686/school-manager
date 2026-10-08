<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\FinanceTransaction;
use App\Models\MoneyAccount;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Carbon;

/**
 * Read-only figures for the finance dashboards (cash basis, same rules as Finance Overview).
 */
class FinanceSummary
{
    public static function monthly(int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);
        $keys = [];
        $labels = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $start->copy()->addMonths($i);
            $keys[] = $m->format('Y-m');
            $labels[] = $m->format('M y');
        }
        $inc = array_fill_keys($keys, 0.0);
        $exp = array_fill_keys($keys, 0.0);

        FeePayment::where('payment_date', '>=', $start->toDateString())
            ->get(['payment_date', 'amount_paid', 'fine_paid'])
            ->each(function ($p) use (&$inc) {
                $k = Carbon::parse($p->payment_date)->format('Y-m');
                if (isset($inc[$k])) {
                    $inc[$k] += (float) $p->amount_paid + (float) $p->fine_paid;
                }
            });

        FinanceTransaction::active()->where('txn_date', '>=', $start->toDateString())
            ->get(['txn_date', 'type', 'amount'])
            ->each(function ($t) use (&$inc, &$exp) {
                $k = Carbon::parse($t->txn_date)->format('Y-m');
                if ($t->type === 'income' && isset($inc[$k])) {
                    $inc[$k] += (float) $t->amount;
                } elseif ($t->type === 'expense' && isset($exp[$k])) {
                    $exp[$k] += (float) $t->amount;
                }
            });

        $income = array_values(array_map(fn($v) => round($v, 2), $inc));
        $expense = array_values(array_map(fn($v) => round($v, 2), $exp));
        $net = [];
        foreach ($income as $i => $v) {
            $net[] = round($v - $expense[$i], 2);
        }

        return ['labels' => $labels, 'income' => $income, 'expense' => $expense, 'net' => $net, 'start' => $start->toDateString()];
    }

    public static function expenseByCategory(string $from, string $to, int $top = 6): array
    {
        $rows = FinanceTransaction::active()->with('category')
            ->where('type', 'expense')->whereBetween('txn_date', [$from, $to])->get()
            ->groupBy(fn($t) => $t->category?->name ?? 'Uncategorised')
            ->map(fn($g) => round((float) $g->sum('amount'), 2))
            ->sortDesc();

        if ($rows->count() > $top) {
            $head = $rows->take($top);
            $head['Other'] = round((float) $rows->slice($top)->sum(), 2);
            $rows = $head;
        }
        $total = (float) $rows->sum();

        return ['rows' => $rows->all(), 'total' => $total];
    }

    public static function accounts()
    {
        return MoneyAccount::where('is_active', true)->orderBy('id')->get()
            ->each(fn($a) => $a->current_balance = $a->balance());
    }

    public static function recent(int $n = 8)
    {
        return FinanceTransaction::active()->with(['account', 'category'])
            ->orderByDesc('txn_date')->orderByDesc('id')->limit($n)->get();
    }

    public static function pendingCount(): int
    {
        return (int) FinanceTransaction::where('status', 'pending')->count();
    }

    /** Billed / collected / outstanding for the current year, discount-aware (same rules as the balance report). */
    public static function feePosition(): array
    {
        $year = AcademicYear::current();
        $out = ['billed' => 0.0, 'paid' => 0.0, 'outstanding' => 0.0, 'debtors' => 0, 'pct' => 0, 'byClass' => []];
        if (! $year) {
            return $out;
        }
        $structures = FeeStructure::where('academic_year_id', $year->id)->get();
        if ($structures->isEmpty()) {
            return $out;
        }

        $assign = FeeBilling::assignments($year->id);
        $paid = FeePayment::where('academic_year_id', $year->id)
            ->selectRaw('student_id, SUM(amount_paid) as t')->groupBy('student_id')->pluck('t', 'student_id');
        $classNames = SchoolClass::pluck('name', 'id');
        $byClass = [];

        foreach (Student::where('is_active', true)->get(['id', 'class_id']) as $s) {
            $mine = $structures->filter(fn($f) => ! $f->class_id || $f->class_id == $s->class_id);
            $discount = array_sum(FeeBilling::allocate($assign->get($s->id, collect()), $mine));
            $fee = (float) $mine->sum('amount') - $discount;
            $p = (float) ($paid[$s->id] ?? 0);
            $bal = max(0, $fee - $p);
            $out['billed'] += $fee;
            $out['paid'] += min($p, $fee > 0 ? $p : 0);
            if ($bal > 0) {
                $out['outstanding'] += $bal;
                $out['debtors']++;
                $byClass[$s->class_id] = ($byClass[$s->class_id] ?? 0) + $bal;
            }
        }

        arsort($byClass);
        foreach (array_slice($byClass, 0, 5, true) as $cid => $amt) {
            $out['byClass'][] = ['name' => $classNames[$cid] ?? 'Unassigned', 'amount' => round($amt, 2)];
        }
        $out['pct'] = $out['billed'] > 0 ? (int) min(100, round($out['paid'] / $out['billed'] * 100)) : 0;

        return $out;
    }

    /** Spend against budget per expense category for the current year (same rules as the Budget page). */
    public static function budgetWatch(int $limit = 6): array
    {
        $year = \App\Models\AcademicYear::current() ?? \App\Models\AcademicYear::orderByDesc('start_date')->first();
        if (! $year) { return []; }
        $from = $year->start_date ? \Carbon\Carbon::parse($year->start_date) : now()->startOfYear();
        $to   = $year->end_date ? \Carbon\Carbon::parse($year->end_date) : now()->endOfYear();
        $budgets = \Illuminate\Support\Facades\DB::table('finance_budgets')
            ->where('academic_year_id', $year->id)->where('amount', '>', 0)
            ->pluck('amount', 'finance_category_id');
        if ($budgets->isEmpty()) { return []; }
        $actual = \App\Models\FinanceTransaction::active()->where('type', 'expense')
            ->whereBetween('txn_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('finance_category_id, SUM(amount) as a')->groupBy('finance_category_id')
            ->pluck('a', 'finance_category_id');
        $names = \App\Models\FinanceCategory::whereIn('id', $budgets->keys())->pluck('name', 'id');
        $rows = [];
        foreach ($budgets as $catId => $amount) {
            $budget = (float) $amount;
            $spent  = (float) ($actual[$catId] ?? 0);
            $pct    = $budget > 0 ? (int) round($spent / $budget * 100) : 0;
            $rows[] = [
                'name'   => $names[$catId] ?? ('Category #' . $catId),
                'budget' => $budget,
                'spent'  => $spent,
                'pct'    => $pct,
                'state'  => $pct >= 100 ? 'over' : ($pct >= 80 ? 'near' : 'ok'),
            ];
        }
        usort($rows, fn($a, $b) => $b['pct'] <=> $a['pct']);
        return [
            'year'    => $year,
            'rows'    => array_slice($rows, 0, $limit),
            'total'   => count($rows),
            'flagged' => count(array_filter($rows, fn($r) => $r['state'] !== 'ok')),
        ];
    }
}
