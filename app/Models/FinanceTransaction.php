<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FinanceTransaction extends Model {
    protected $fillable = ['type','money_account_id','finance_category_id','txn_date','amount','payee',
        'reference_no','description','attachment_path','status','voided_at','voided_by','void_reason','created_by'];
    protected $casts = ['txn_date' => 'date', 'amount' => 'decimal:2', 'voided_at' => 'datetime'];

    public function account()  { return $this->belongsTo(MoneyAccount::class, 'money_account_id'); }
    public function category() { return $this->belongsTo(FinanceCategory::class, 'finance_category_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}
