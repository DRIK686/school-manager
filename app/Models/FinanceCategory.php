<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FinanceCategory extends Model {
    protected $fillable = ['name', 'type', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function transactions() { return $this->hasMany(FinanceTransaction::class); }
}
