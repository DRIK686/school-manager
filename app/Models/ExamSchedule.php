<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model {
    protected $fillable = [
        'exam_type_id','class_id','subject_id','exam_date',
        'start_time','end_time','room','max_marks','passing_marks',
    ];
    protected $casts = ['exam_date' => 'date'];

    public function examType() { return $this->belongsTo(ExamType::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function marks() { return $this->hasMany(StudentMark::class); }
}
