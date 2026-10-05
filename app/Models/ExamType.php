<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExamType extends Model {
    protected $fillable = ['name','description','academic_year_id','is_published'];
    protected $casts = ['is_published' => 'boolean'];

    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function schedules() { return $this->hasMany(ExamSchedule::class); }
}
