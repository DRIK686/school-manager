<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'school_name', 'logo', 'address', 'phone', 'email',
        'website', 'motto', 'academic_year', 'currency_symbol',
        'timezone', 'theme_color', 'sidebar_color', 'accent_color',
        'mail_host', 'mail_port', 'mail_username', 'mail_password',
        'mail_encryption', 'mail_from_address', 'mail_from_name',
    ];

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'school_name'    => 'SchoolManager',
            'currency_symbol'=> 'GH₵',
            'timezone'       => 'Africa/Accra',
            'theme_color'    => '#0f766e',
            'sidebar_color'  => '#0f766e',
            'accent_color'   => '#14b8a6',
        ]);
    }
}
