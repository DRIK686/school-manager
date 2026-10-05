<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LessonPlan extends Model {
    protected $fillable = [
        'teacher_id','class_id','subject_id','academic_year_id',
        'week_no','topic','objectives','activities','resources',
        'status','admin_remarks','file_path','file_original_name'
    ];
    public function teacher()      { return $this->belongsTo(User::class, 'teacher_id'); }
    public function schoolClass()  { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function subject()      { return $this->belongsTo(Subject::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}
