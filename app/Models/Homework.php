<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Homework extends Model {
    protected $fillable = [
        'class_id','subject_id','teacher_id','academic_year_id',
        'title','description','due_date','attachment_path','attachment_name'
    ];
    protected $casts = ['due_date' => 'date'];
    public function schoolClass()  { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function subject()      { return $this->belongsTo(Subject::class); }
    public function teacher()      { return $this->belongsTo(User::class, 'teacher_id'); }
    public function submissions() { return $this->hasMany(HomeworkSubmission::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}
