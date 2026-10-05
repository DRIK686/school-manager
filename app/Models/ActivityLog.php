<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    const UPDATED_AT = null;
    protected $fillable = [
        'user_id','user_name','user_role','action','description',
        'subject_type','subject_id','ip_address',
    ];
}
