<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentDocument extends Model {
    protected $fillable = ['student_id','document_type','file_path','file_name'];
    public function student() { return $this->belongsTo(Student::class); }
}
