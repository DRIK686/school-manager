<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PromotionRecommendation extends Model {
    protected $fillable = [
        'teacher_id','student_id','from_academic_year_id','from_class_id','from_section_id',
        'recommended_action','recommended_class_id','recommended_section_id','remarks','status',
    ];
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function student() { return $this->belongsTo(Student::class); }
    public function fromClass() { return $this->belongsTo(SchoolClass::class, 'from_class_id'); }
    public function recommendedClass() { return $this->belongsTo(SchoolClass::class, 'recommended_class_id'); }
}
