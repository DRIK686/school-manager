<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model {
    protected $fillable = [
        'homework_id','student_id','answer',
        'score','feedback','status',
        'submitted_at','graded_by','graded_at'
    ];
    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at'    => 'datetime',
    ];

    public function homework() { return $this->belongsTo(Homework::class); }
    public function student()  { return $this->belongsTo(Student::class); }
    public function gradedBy() { return $this->belongsTo(User::class, 'graded_by'); }
}
