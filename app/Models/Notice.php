<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model {
    protected $fillable = ['posted_by','title','body','audience','is_published','expires_at'];
    protected $casts    = ['is_published' => 'boolean', 'expires_at' => 'date'];
    public function poster() { return $this->belongsTo(User::class, 'posted_by'); }

    public function scopeActive($query) {
        return $query->where('is_published', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', today()));
    }
}
