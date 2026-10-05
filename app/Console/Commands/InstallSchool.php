<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstallSchool extends Command
{
    protected $signature = 'school:install';
    protected $description = 'First-time setup for a new school (settings, academic year, grade scale, Super Admin)';

    public function handle(): int
    {
        if (DB::table('users')->count() > 0 || DB::table('school_settings')->count() > 0) {
            $this->error('This database already has users or school settings. school:install only runs on a fresh database.');
            return self::FAILURE;
        }

        $this->info('School details');
        $name = trim((string) $this->ask('School name'));
        if ($name === '') {
            return $this->stopWithError('School name is required.');
        }
        $motto    = trim((string) $this->ask('Motto (optional)', ''));
        $address  = trim((string) $this->ask('Address (optional)', ''));
        $phone    = trim((string) $this->ask('Phone (optional)', ''));
        $email    = trim((string) $this->ask('School email (optional)', ''));
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->stopWithError('School email is not valid.');
        }
        $timezone = trim((string) $this->ask('Timezone', 'Africa/Accra'));
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            return $this->stopWithError('Unknown timezone.');
        }
        $currency = trim((string) $this->ask('Currency symbol', "GH\u{20B5}"));

        $theme   = strtolower(trim((string) $this->ask('Theme colour (hex)', '#0f766e')));
        $sidebar = strtolower(trim((string) $this->ask('Sidebar colour (hex)', '#134e4a')));
        $accent  = strtolower(trim((string) $this->ask('Accent colour (hex)', '#14b8a6')));
        foreach ([$theme, $sidebar, $accent] as $c) {
            if (! preg_match('/^#[0-9a-f]{6}$/', $c)) {
                return $this->stopWithError('Colours must be 6-digit hex values such as #0f766e.');
            }
        }

        $y = (int) date('Y');
        if ((int) date('n') < 9) {
            $y--;
        }
        $yearName = trim((string) $this->ask('First academic year', $y . '/' . ($y + 1)));
        if (! preg_match('#^(\d{4})/(\d{4})$#', $yearName, $m) || (int) $m[2] !== (int) $m[1] + 1) {
            return $this->stopWithError('Academic year must look like 2026/2027.');
        }
        $start = $m[1] . '-09-01';
        $end   = $m[2] . '-07-31';

        $this->info('Super Admin account');
        $adminName  = trim((string) $this->ask('Admin full name'));
        $adminEmail = trim((string) $this->ask('Admin email'));
        if ($adminName === '' || ! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->stopWithError('Admin name and a valid admin email are required.');
        }
        $password = (string) $this->secret('Password (min 10 characters)');
        $confirm  = (string) $this->secret('Confirm password');
        if (strlen($password) < 10 || $password !== $confirm) {
            return $this->stopWithError('Password must be at least 10 characters and match the confirmation.');
        }

        // Roles + neutral website content (insert-only seeders)
        $this->call('db:seed', ['--force' => true]);

        $roleId = DB::table('roles')->where('slug', 'super_admin')->value('id');
        if (! $roleId) {
            return $this->stopWithError('The super_admin role is missing; seeding failed.');
        }

        $scale = [
            ['A1', 80, 100, 1, 'Excellent'],
            ['B2', 70, 79.99, 2, 'Very Good'],
            ['B3', 60, 69.99, 3, 'Good'],
            ['C4', 55, 59.99, 4, 'Credit'],
            ['C5', 50, 54.99, 5, 'Credit'],
            ['C6', 45, 49.99, 6, 'Credit'],
            ['D7', 40, 44.99, 7, 'Pass'],
            ['E8', 35, 39.99, 8, 'Pass'],
            ['F9', 0, 34.99, 9, 'Fail'],
        ];

        DB::transaction(function () use (
            $name, $motto, $address, $phone, $email, $timezone, $currency,
            $theme, $sidebar, $accent, $yearName, $start, $end,
            $adminName, $adminEmail, $password, $roleId, $scale
        ) {
            $now = now();

            DB::table('school_settings')->insert([
                'school_name' => $name,
                'address' => $address ?: null,
                'phone' => $phone ?: null,
                'email' => $email ?: null,
                'website' => null,
                'motto' => $motto ?: null,
                'academic_year' => $yearName,
                'currency_symbol' => $currency,
                'timezone' => $timezone,
                'theme_color' => $theme,
                'sidebar_color' => $sidebar,
                'accent_color' => $accent,
                'created_at' => $now, 'updated_at' => $now,
            ]);

            // Keep the public website in step with the same answers
            $ws = fn ($key, $value) => DB::table('website_settings')
                ->where('key', $key)->update(['value' => $value, 'updated_at' => $now]);
            $ws('hero_headline', 'Welcome to ' . $name);
            $ws('contact_address', $address);
            $ws('contact_phone', $phone);
            $ws('contact_email', $email);
            $ws('footer_tagline', $motto);
            $ws('primary_color', $theme);
            $ws('secondary_color', $accent);
            $ws('hero_bg_color', $theme);

            $yearId = DB::table('academic_years')->insertGetId([
                'name' => $yearName, 'start_date' => $start, 'end_date' => $end,
                'is_current' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);

            foreach ($scale as [$grade, $min, $max, $points, $remark]) {
                DB::table('grade_scales')->insert([
                    'academic_year_id' => $yearId, 'grade' => $grade,
                    'min_mark' => $min, 'max_mark' => $max, 'points' => $points,
                    'remark' => $remark, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            DB::table('users')->insert([
                'name' => $adminName, 'email' => $adminEmail,
                'password' => Hash::make($password), 'role_id' => $roleId,
                'is_active' => 1, 'email_verified_at' => $now,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        });

        $this->newLine();
        $this->info('Done. Next steps:');
        $this->line(' - Set APP_URL, mail and (optional) ANTHROPIC_API_KEY in .env, then run: php artisan optimize:clear');
        $this->line(' - Run: php artisan storage:link');
        $this->line(' - Log in as the Super Admin and upload the logo in Settings.');
        $this->line(' - Replace the placeholder text in Website CMS.');

        return self::SUCCESS;
    }

    private function stopWithError(string $message): int
    {
        $this->error($message);
        return self::FAILURE;
    }
}
