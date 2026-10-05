<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isTeacher()) {
            $school = SchoolSetting::first();
            return view('teacher.profile', compact('user', 'school'));
        }
        return view('admin.profile', compact('user'));
    }
    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        $data = $request->only(['name', 'email', 'phone']);
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $data['profile_photo'] = $path;
        }
        $user->update($data);
        return back()->with('success', 'Profile updated successfully.');
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->with('tab', 'password');
        }
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed successfully.')->with('tab', 'password');
    }
}
