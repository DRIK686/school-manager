<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model {
    protected $fillable = [
        'receipt_no','student_id','academic_year_id','fee_structure_id',
        'amount_paid','fine_paid','discount_amount','payment_date',
        'payment_mode','reference_no','remarks','collected_by',
        'money_account_id','status','voided_at','voided_by','void_reason',
    ];
    protected $casts = ['payment_date' => 'date', 'amount_paid' => 'decimal:2'];

    // Voided payments are hidden everywhere (reports, balances, dashboard) unless a query opts out.
    protected static function booted(): void
    {
        static::addGlobalScope('active', function ($q) {
            $q->where($q->getModel()->getTable() . '.status', 'active');
        });
    }

    public function account() { return $this->belongsTo(MoneyAccount::class, 'money_account_id'); }

    public function getCashReceivedAttribute(): float
    {
        return (float) $this->amount_paid + (float) $this->fine_paid;
    }

    public function student() { return $this->belongsTo(Student::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function feeStructure() { return $this->belongsTo(FeeStructure::class); }
    public function collectedBy() { return $this->belongsTo(User::class, 'collected_by'); }

    public static function generateReceiptNo(): string {
        $last = static::withoutGlobalScopes()->lockForUpdate()->orderByDesc('id')->first();
        $seq  = $last ? (int) substr($last->receipt_no, 4) + 1 : 1;
        return 'RCP-' . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }
}
