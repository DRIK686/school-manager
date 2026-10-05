<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        $school = SchoolSetting::first();
        return view('auth.forgot-password', compact('school'));
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        MailConfigService::applyFromSettings();

        $status = Password::sendResetLink($request->only('email'));

        // Always show the same message regardless of whether the email
        // exists, so this endpoint can't be used to enumerate accounts.
        return back()->with('status', 'If that email address is registered, a password reset link has been sent.');
    }

    public function showResetForm(Request $request, string $token)
    {
        $school = SchoolSetting::first();
        return view('auth.reset-password', [
            'school' => $school,
            'token'  => $token,
            'email'  => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Your password has been reset. You can now sign in.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
