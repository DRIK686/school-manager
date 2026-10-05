<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class WebsiteHeroMedia extends Model {
    protected $table = 'website_hero_media';
    protected $fillable = ['file_path','type','sort_order'];
}
