<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model {
    protected $fillable = ['name', 'start_date', 'end_date', 'is_current'];
    protected $casts = ['is_current' => 'boolean', 'start_date' => 'date', 'end_date' => 'date'];

    public static function current(): ?self {
        return static::where('is_current', true)->first();
    }

    public function setCurrent(): void {
        static::query()->update(['is_current' => false]);
        $this->update(['is_current' => true]);
    }
}
