<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LoginController extends Controller
{
    public function showLogin()
    {
        $school = \App\Models\SchoolSetting::first();
        return view('auth.login', compact('school'));
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            if (!$user->is_active) {
                Auth::logout();
                return back()->with('error', 'Your account has been deactivated.');
            }
            ActivityLogger::log('login', $user->name . ' logged in.');
            if ($user->isTeacher()) {
                return redirect()->route('teacher.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }
        ActivityLogger::log('login_failed', 'Failed login attempt for email: ' . $request->email);
        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLogger::log('logout', $user->name . ' logged out.');
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
