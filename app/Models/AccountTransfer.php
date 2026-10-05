<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AccountTransfer extends Model {
    protected $fillable = ['from_account_id','to_account_id','amount','transfer_date','reference_no','note',
        'status','voided_at','voided_by','void_reason','created_by'];
    protected $casts = ['transfer_date' => 'date', 'amount' => 'decimal:2', 'voided_at' => 'datetime'];

    public function from() { return $this->belongsTo(MoneyAccount::class, 'from_account_id'); }
    public function to()   { return $this->belongsTo(MoneyAccount::class, 'to_account_id'); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}
