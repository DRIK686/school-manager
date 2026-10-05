<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model {
    protected $fillable = ['class_id', 'subject_id', 'teacher_id'];
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
}
