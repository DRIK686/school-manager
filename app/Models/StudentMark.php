<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StudentMark extends Model {
    protected $fillable = [
        'student_id','exam_schedule_id','marks_obtained',
        'sba_score','exam_score','class_average',
        'grade','remarks','is_absent'
    ];
    protected $casts = [
        'is_absent'     => 'boolean',
        'marks_obtained'=> 'decimal:2',
        'sba_score'     => 'decimal:2',
        'exam_score'    => 'decimal:2',
        'class_average' => 'decimal:2',
    ];
    public function student()      { return $this->belongsTo(Student::class); }
    public function examSchedule() { return $this->belongsTo(ExamSchedule::class); }
}
