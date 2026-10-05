<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebsiteNews extends Model {
    protected $table = 'website_news';
    protected $fillable = ['title','slug','excerpt','body','image','category','published','published_at','sort_order'];
    protected $casts = ['published' => 'boolean', 'published_at' => 'date'];

    public static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->slug) {
                $model->slug = Str::slug($model->title) . '-' . uniqid();
            }
            if (!$model->published_at) {
                $model->published_at = now()->toDateString();
            }
        });
    }

    public function scopePublished($q) { return $q->where('published', true); }
}
