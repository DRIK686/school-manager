<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasFactory, Notifiable;

    protected $fillable = [
        'name','email','password','role_id','phone','profile_photo','is_active',
    ];

    protected $hidden = ['password','remember_token'];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role() { return $this->belongsTo(Role::class); }
    public function staffProfile() { return $this->hasOne(StaffProfile::class); }
    public function staffDocuments() { return $this->hasMany(StaffDocument::class); }

    public function hasRole(string $slug): bool { return $this->role?->slug === $slug; }
    public function hasAnyRole(array $slugs): bool { return in_array($this->role?->slug, $slugs); }
    public function isAdmin(): bool { return $this->hasAnyRole(['super_admin','admin']); }
    public function isTeacher(): bool { return $this->hasRole('teacher'); }
    public function isClassTeacherOf(int $classId): bool { return SchoolClass::where('id', $classId)->where('class_teacher_id', $this->id)->exists(); }

    /** Class IDs this teacher is connected to: class teacher of, or assigned a subject in. */
    public function teachingClassIds(): \Illuminate\Support\Collection
    {
        return SchoolClass::where('class_teacher_id', $this->id)->pluck('id')
            ->merge(ClassSubject::where('teacher_id', $this->id)->pluck('class_id'))
            ->map(fn ($i) => (int) $i)->unique()->values();
    }

    public function teachesClass(int $classId): bool
    {
        return $this->teachingClassIds()->contains($classId);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($user) {
            if ($user->hasRole('super_admin')) {
                throw new \Exception('The Super Admin account cannot be deleted.');
            }
        });
    }
}
