<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FeeDiscount extends Model {
    protected $fillable = ['name', 'discount_type', 'value'];
    public function calculateDiscount(float $amount): float {
        return $this->discount_type === 'percent'
            ? round($amount * $this->value / 100, 2)
            : min($this->value, $amount);
    }
}
