<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentTermReport extends Model {
    protected $fillable = [
        'student_id','exam_type_id','academic_year_id',
        'conduct','talent_interest','teacher_remarks','promoted_to',
        'attendance_present','attendance_total',
        'vacation_date','reopening_date',
        'class_teacher','principal',
    ];
    protected $casts = ['vacation_date'=>'date','reopening_date'=>'date'];

    public function student()    { return $this->belongsTo(Student::class); }
    public function examType()   { return $this->belongsTo(ExamType::class); }
    public function academicYear(){ return $this->belongsTo(AcademicYear::class); }
}
