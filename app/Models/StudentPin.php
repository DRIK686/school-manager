<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentPin extends Model {
    protected $fillable = ['student_id','pin','plain_pin','is_first_login','last_login_at'];
    protected $casts    = ['is_first_login' => 'boolean', 'last_login_at' => 'datetime'];
    public function student() { return $this->belongsTo(Student::class); }
}
