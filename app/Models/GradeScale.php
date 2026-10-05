<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model {
    protected $fillable = ['academic_year_id','grade','min_mark','max_mark','points','remark'];

    public function academicYear() { return $this->belongsTo(AcademicYear::class); }

    public static function getGrade(float $marks, int $yearId): ?self {
        return static::where('academic_year_id', $yearId)
            ->where('min_mark', '<=', $marks)
            ->where('max_mark', '>=', $marks)
            ->first();
    }
}
