<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model {
    protected $fillable = ['student_id','relation','full_name','phone','email','occupation','address','user_id'];
    public function student() { return $this->belongsTo(Student::class); }
    public function user() { return $this->belongsTo(User::class); }
}
