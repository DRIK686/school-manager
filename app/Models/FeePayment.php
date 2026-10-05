<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model {
    protected $fillable = [
        'receipt_no','student_id','academic_year_id','fee_structure_id',
        'amount_paid','fine_paid','discount_amount','payment_date',
        'payment_mode','reference_no','remarks','collected_by',
    ];
    protected $casts = ['payment_date' => 'date', 'amount_paid' => 'decimal:2'];

    public function student() { return $this->belongsTo(Student::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function feeStructure() { return $this->belongsTo(FeeStructure::class); }
    public function collectedBy() { return $this->belongsTo(User::class, 'collected_by'); }

    public static function generateReceiptNo(): string {
        $last = static::orderByDesc('id')->first();
        $seq  = $last ? (int) substr($last->receipt_no, 4) + 1 : 1;
        return 'RCP-' . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }
}
