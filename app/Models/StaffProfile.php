<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model {
    protected $fillable = [
        'user_id','employee_id','designation','department','joining_date',
        'qualification','experience','gender','dob','blood_group',
        'marital_status','phone','emergency_contact','address',
        'basic_salary','bank_name','bank_account','is_active',
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'is_active' => 'boolean',
        'basic_salary' => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public static function generateEmployeeId(): string {
        $last = static::orderByDesc('id')->first();
        $seq  = $last ? (int) substr($last->employee_id, 3) + 1 : 1;
        return 'EMP' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
