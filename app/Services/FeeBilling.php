<?php
namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FeeBilling
{
    /** Discount assignments for an academic year, grouped by student_id. */
    public static function assignments(?int $yearId, ?array $studentIds = null): Collection
    {
        return DB::table('student_fee_discounts as sd')
            ->join('fee_discounts as d', 'sd.fee_discount_id', '=', 'd.id')
            ->where('sd.academic_year_id', $yearId)
            ->when($studentIds, fn($q) => $q->whereIn('sd.student_id', $studentIds))
            ->get(['sd.student_id', 'd.name', 'd.discount_type', 'd.value'])
            ->groupBy('student_id');
    }

    /** Split a student's discounts over their applicable fee structures. Returns [structure_id => discount]. */
    public static function allocate(Collection $assigned, Collection $structures): array
    {
        $structures = $structures->sortBy('id')->values();
        $out = [];
        foreach ($structures as $s) { $out[$s->id] = 0.0; }
        $fixedPool = 0.0;

        foreach ($assigned as $a) {
            if ($a->discount_type === 'percent') {
                foreach ($structures as $s) {
                    $out[$s->id] += round((float) $s->amount * (float) $a->value / 100, 2);
                }
            } else {
                $fixedPool += (float) $a->value;
            }
        }
        foreach ($structures as $s) { $out[$s->id] = min($out[$s->id], (float) $s->amount); }
        foreach ($structures as $s) {
            if ($fixedPool <= 0) break;
            $take = min((float) $s->amount - $out[$s->id], $fixedPool);
            $out[$s->id] += $take;
            $fixedPool -= $take;
        }
        return $out;
    }

    public static function discounts(int $studentId, ?int $yearId, Collection $structures): array
    {
        $assigned = self::assignments($yearId, [$studentId])->get($studentId, collect());
        return self::allocate($assigned, $structures);
    }
}
