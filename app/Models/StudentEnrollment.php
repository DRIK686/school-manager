<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model {
    protected $fillable = [
        'student_id','academic_year_id','class_id','section_id','roll_no','status','remarks',
    ];
    public function student() { return $this->belongsTo(Student::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
}
