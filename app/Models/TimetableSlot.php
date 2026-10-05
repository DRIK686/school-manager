<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TimetableSlot extends Model {
    protected $fillable = ['slot_no','label','start_time','end_time','is_break'];
    protected $casts    = ['is_break' => 'boolean'];
    public function timetables() { return $this->hasMany(Timetable::class, 'slot_id'); }
}
