<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StaffDocument extends Model {
    protected $fillable = ['user_id','document_type','file_path','file_name'];
    public function user() { return $this->belongsTo(User::class); }
}
