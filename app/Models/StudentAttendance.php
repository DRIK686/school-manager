<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model {
    protected $table = 'student_attendance';
    protected $fillable = [
        'student_id','class_id','section_id','academic_year_id',
        'date','status','remarks','marked_by','taken_at',
    ];
    protected $casts = ['date' => 'date', 'taken_at' => 'datetime'];

    public function student() { return $this->belongsTo(Student::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function markedBy() { return $this->belongsTo(User::class, 'marked_by'); }

    public static function getStatusColor(string $status): string {
        return match($status) {
            'present' => 'green',
            'absent'  => 'red',
            'late'    => 'yellow',
            'holiday' => 'blue',
            default   => 'gray',
        };
    }
}
