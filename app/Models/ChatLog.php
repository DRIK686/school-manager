<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ChatLog extends Model {
    protected $fillable = ['session_id', 'ip_address', 'question', 'answer', 'was_grounded'];
    protected $casts = ['was_grounded' => 'boolean'];
}
