<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model {
    protected $fillable = ['academic_year_id','class_id','fee_category_id','amount','due_date','fine_per_day'];
    protected $casts = ['due_date' => 'date', 'amount' => 'decimal:2', 'fine_per_day' => 'decimal:2'];

    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function feeCategory() { return $this->belongsTo(FeeCategory::class); }
    public function payments() { return $this->hasMany(FeePayment::class); }

    public function calculateFine(): float {
        if (!$this->due_date || !$this->fine_per_day) return 0;
        $days = now()->diffInDays($this->due_date, false);
        return $days < 0 ? abs($days) * $this->fine_per_day : 0;
    }

    public function totalPaidByStudent(int $studentId): float {
        return $this->payments()->where('student_id', $studentId)->sum('amount_paid');
    }

    public function balanceForStudent(int $studentId): float {
        return $this->amount - $this->totalPaidByStudent($studentId);
    }
}
