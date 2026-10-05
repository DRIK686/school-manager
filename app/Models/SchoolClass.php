<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model {
    protected $table = 'classes';
    protected $fillable = ['name', 'numeric_order', 'class_teacher_id'];

    public function sections() { return $this->hasMany(Section::class, 'class_id'); }
    public function students() { return $this->hasMany(Student::class, 'class_id'); }
    public function subjects() { return $this->belongsToMany(Subject::class, 'class_subjects')->withPivot('teacher_id')->withTimestamps(); }
    public function classSubjects() { return $this->hasMany(ClassSubject::class, 'class_id'); }
    public function classTeacher() { return $this->belongsTo(User::class, 'class_teacher_id'); }
}
