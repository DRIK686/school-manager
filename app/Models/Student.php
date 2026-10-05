<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model {
    use SoftDeletes;

    protected $fillable = [
        'admission_no','admission_date','user_id','class_id','section_id',
        'academic_year_id','roll_no','first_name','last_name','other_name',
        'gender','dob','blood_group','religion','nationality','mother_tongue',
        'phone','address','previous_school','previous_class',
        'guardian_name', 'guardian_relationship', 'guardian_phone',
        'guardian_email', 'guardian_occupation','profile_photo','is_active',
    ];

    protected $casts = [
        'dob' => 'date',
        'admission_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function getFullNameAttribute(): string {
        return trim("{$this->first_name} {$this->other_name} {$this->last_name}");
    }

    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function parents() { return $this->hasMany(StudentParent::class); }
    public function documents() { return $this->hasMany(StudentDocument::class); }
    public function user() { return $this->belongsTo(User::class); }

    public static function generateAdmissionNo(): string {
        $year = date('Y');
        $last = static::withTrashed()
            ->where('admission_no', 'like', "{$year}/%")
            ->orderByDesc('id')
            ->first();
        $seq = $last ? (int) explode('/', $last->admission_no)[1] + 1 : 1;
        return $year . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function pin()           { return $this->hasOne(StudentPin::class); }
    public function homework()      { return $this->hasManyThrough(Homework::class, SchoolClass::class, 'id', 'class_id', 'class_id', 'id'); }
    public function feeRecords()    { return $this->hasMany(FeePayment::class); }
    public function attendances()   { return $this->hasMany(StudentAttendance::class); }
    public function marks()         { return $this->hasMany(StudentMark::class); }
    public function enrollments()   { return $this->hasMany(StudentEnrollment::class); }

    public function getStatusLabelAttribute(): string {
        if ($this->is_active) return 'Active';
        $latest = $this->enrollments()->orderByDesc('academic_year_id')->first();
        return match($latest?->status) {
            'graduated' => 'Graduated',
            'withdrawn' => 'Withdrawn',
            default     => 'Inactive',
        };
    }
    public function generatePin(): string
    {
        $plain = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        StudentPin::updateOrCreate(
            ['student_id' => $this->id],
            [
                'pin'            => bcrypt($plain),
                'plain_pin'      => $plain,
                'is_first_login' => true,
            ]
        );
        return $plain;
    }

}
