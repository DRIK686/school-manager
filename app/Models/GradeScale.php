<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model {
    protected $fillable = ['academic_year_id','grade','min_mark','max_mark','points','remark'];

    public function academicYear() { return $this->belongsTo(AcademicYear::class); }

    public static function getGrade(float $marks, int $yearId): ?self {
        // Highest band whose minimum the score has reached. Order-independent, so shared
        // boundaries (90.00 in two bands) and 0.01 gaps (79.995) always resolve to one grade.
        return static::where('academic_year_id', $yearId)
            ->where('min_mark', '<=', $marks)
            ->orderByDesc('min_mark')
            ->first();
    }
}
