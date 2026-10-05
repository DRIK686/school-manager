<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model {
    protected $fillable = [
        'academic_year_id','class_id','section_id','day_of_week',
        'slot_id','subject_id','teacher_id','custom_label'
    ];

    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function schoolClass()  { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section()      { return $this->belongsTo(Section::class); }
    public function slot()         { return $this->belongsTo(TimetableSlot::class, 'slot_id'); }
    public function subject()      { return $this->belongsTo(Subject::class); }
    public function teacher()      { return $this->belongsTo(User::class, 'teacher_id'); }
}
