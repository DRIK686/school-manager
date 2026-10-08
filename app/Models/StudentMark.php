<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StudentMark extends Model {
    protected $fillable = [
        'student_id','exam_schedule_id','marks_obtained',
        'sba_score','exam_score','class_average',
        'grade','remarks','is_absent'
    ];
    protected $casts = [
        'is_absent'     => 'boolean',
        'marks_obtained'=> 'decimal:2',
        'sba_score'     => 'decimal:2',
        'exam_score'    => 'decimal:2',
        'class_average' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Whoever saves a mark (admin, teacher, import), the grade and remark follow the grade scale.
        static::saving(function (self $mark) {
            static::applyGrade($mark);
        });
    }

    /**
     * Fill grade/remarks from the grade scale of the exam's own academic year.
     * Only runs when the grade is missing or the mark itself changed.
     */
    public static function applyGrade(self $mark): void
    {
        if ($mark->is_absent || $mark->marks_obtained === null) {
            return;
        }
        if (! empty($mark->grade) && ! $mark->isDirty('marks_obtained')) {
            return;
        }

        $schedule = ExamSchedule::find($mark->exam_schedule_id);
        if (! $schedule) {
            return;
        }
        $max = (float) ($schedule->max_marks ?: 100);
        if ($max <= 0) {
            return;
        }

        $yearId = ExamType::whereKey($schedule->exam_type_id)->value('academic_year_id')
            ?? AcademicYear::current()?->id;

        $scale = GradeScale::getGrade(((float) $mark->marks_obtained) / $max * 100, $yearId);
        if (! $scale) {
            return;
        }

        $mark->grade = $scale->grade;

        // Keep a teacher's own comment; only (re)fill auto-generated scale remarks.
        $auto = GradeScale::where('academic_year_id', $yearId)->pluck('remark')->all();
        if (empty($mark->remarks) || ($mark->isDirty('marks_obtained') && in_array($mark->remarks, $auto, true))) {
            $mark->remarks = $scale->getAttribute('remark');
        }
    }

    public function student()      { return $this->belongsTo(Student::class); }
    public function examSchedule() { return $this->belongsTo(ExamSchedule::class); }
}
