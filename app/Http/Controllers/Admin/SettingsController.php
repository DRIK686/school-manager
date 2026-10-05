<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function index()
    {
        $school = SchoolSetting::current();
        return view('admin.settings', compact('school'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'school_name'     => 'required|string|max:255',
            'motto'           => 'nullable|string|max:255',
            'address'         => 'nullable|string|max:500',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|string|max:255',
            'currency_symbol' => 'nullable|string|max:10',
            'academic_year'   => 'nullable|string|max:20',
            'sidebar_color'   => 'nullable|string|max:7',
            'accent_color'    => 'nullable|string|max:7',
            'theme_color'     => 'nullable|string|max:7',
            'logo'            => 'nullable|image|max:2048',
        ]);

        $school = SchoolSetting::current();

        $data = $request->except(['_token','logo','test_email','test_email_to']);

        // Mail/SMTP settings are Super Admin-only. Strip them for anyone
        // else, even if a crafted request tries to submit them directly.
        $mailFields = ['mail_host','mail_port','mail_username','mail_password','mail_encryption','mail_from_address','mail_from_name'];
        if (!auth()->user()->hasRole('super_admin')) {
            $data = collect($data)->except($mailFields)->all();
        } elseif (empty($data['mail_password'])) {
            // Don't overwrite the stored password with a blank value
            // when the admin leaves the field untouched on save.
            unset($data['mail_password']);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('school', 'public');
            $data['logo'] = $path;
        }

        $school->update($data);
        ActivityLogger::log('settings.update', 'Updated school settings.' . (auth()->user()->hasRole('super_admin') && $request->hasAny(['mail_host','mail_username']) ? ' (including mail/SMTP settings)' : ''));

        // ── SMTP Test ────────────────────────────────────────────
        if ($request->input('test_email') == '1') {
            abort_unless(auth()->user()->hasRole('super_admin'), 403);
            $testTo = $request->input('test_email_to') ?: $request->input('mail_username');

            if (!$testTo) {
                return back()->with('smtp_error', 'Please enter a recipient email address for the test.');
            }

            // Apply SMTP config from form values at runtime
            Config::set('mail.default',                    'smtp');
            Config::set('mail.mailers.smtp.host',          $request->input('mail_host'));
            Config::set('mail.mailers.smtp.port',          (int) $request->input('mail_port', 587));
            Config::set('mail.mailers.smtp.username',      $request->input('mail_username'));
            Config::set('mail.mailers.smtp.password',      $request->input('mail_password') ?: $school->mail_password);
            Config::set('mail.mailers.smtp.encryption',    $request->input('mail_encryption') ?: null);
            Config::set('mail.from.address',               $request->input('mail_from_address') ?: $request->input('mail_username'));
            Config::set('mail.from.name',                  $request->input('mail_from_name') ?: $school->school_name);

            try {
                Mail::raw(
                    "Hello from {$school->school_name}!\n\n"
                    . "Your SMTP settings are configured correctly.\n\n"
                    . "Host: {$request->mail_host}:{$request->mail_port}\n"
                    . "Encryption: {$request->mail_encryption}\n"
                    . "From: {$request->mail_from_address}",
                    fn($m) => $m->to($testTo)
                               ->subject('✅ SMTP Test — ' . $school->school_name)
                );

                return back()->with('smtp_success', "Test email sent to {$testTo}. Check your inbox!");
            } catch (\Exception $e) {
                return back()->with('smtp_error', $e->getMessage());
            }
        }

        return back()->with('success', 'School settings saved successfully.');
    }
}
