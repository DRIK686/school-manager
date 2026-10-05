<?php
namespace App\Services;

use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Config;

class MailConfigService
{
    public static function applyFromSettings(): void
    {
        $school = SchoolSetting::first();
        if (!$school || !$school->mail_host) return;

        Config::set('mail.mailers.smtp.host',       $school->mail_host);
        Config::set('mail.mailers.smtp.port',       $school->mail_port ?? 587);
        Config::set('mail.mailers.smtp.username',   $school->mail_username);
        Config::set('mail.mailers.smtp.password',   $school->mail_password);
        Config::set('mail.mailers.smtp.encryption', $school->mail_encryption ?? 'tls');
        Config::set('mail.default',                 'smtp');
        Config::set('mail.from.address',            $school->mail_from_address);
        Config::set('mail.from.name',               $school->mail_from_name ?? $school->school_name);
    }
}
