<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model {
    protected $fillable = ['name', 'code', 'type', 'color'];
    public function classes() { return $this->belongsToMany(SchoolClass::class, 'class_subjects', 'subject_id', 'class_id')->withPivot('teacher_id')->withTimestamps(); }
}
