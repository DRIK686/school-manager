<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebsiteItem extends Model {
    protected $fillable = [
        'type','title','subtitle','description',
        'image','icon','badge','link',
        'sort_order','is_active','meta','category'
    ];
    protected $casts = ['is_active' => 'boolean', 'meta' => 'array'];
    public function scopeOfType($q, string $type) { return $q->where('type', $type)->where('is_active', true)->orderBy('sort_order'); }
}
