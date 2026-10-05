<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\StaffDocument;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role','staffProfile'])
            ->whereHas('role', fn($q) => $q->whereNotIn('slug',['super_admin','parent','student']))
            ->orderBy('name');

        if ($request->role_id) $query->where('role_id', $request->role_id);
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name','like',"%{$request->search}%")
                  ->orWhere('email','like',"%{$request->search}%");
            });
        }

        $staff = $query->paginate(20)->withQueryString();
        $roles = Role::whereNotIn('slug',['super_admin','parent','student'])->get();
        return view('admin.staff.index', compact('staff','roles'));
    }

    public function create()
    {
        $roles = Role::whereNotIn('slug',['super_admin','parent','student'])->get();
        $employeeId = StaffProfile::generateEmployeeId();
        return view('admin.staff.create', compact('roles','employeeId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|string|min:6',
            'role_id'       => 'required|exists:roles,id',
            'designation'   => 'nullable|string|max:100',
            'department'    => 'nullable|string|max:100',
            'joining_date'  => 'required|date',
            'gender'        => 'required|in:male,female,other',
            'dob'           => 'nullable|date',
            'phone'         => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:200',
            'basic_salary'  => 'nullable|numeric|min:0',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        $newUser = null;
        DB::transaction(function() use ($request, &$newUser) {
            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')->store('staff/photos','public');
            }

            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'role_id'       => $request->role_id,
                'phone'         => $request->phone,
                'profile_photo' => $photoPath,
                'is_active'     => true,
            ]);

            $user->staffProfile()->create([
                'employee_id'      => StaffProfile::generateEmployeeId(),
                'designation'      => $request->designation,
                'department'       => $request->department,
                'joining_date'     => $request->joining_date,
                'qualification'    => $request->qualification,
                'experience'       => $request->experience,
                'gender'           => $request->gender,
                'dob'              => $request->dob,
                'blood_group'      => $request->blood_group,
                'marital_status'   => $request->marital_status ?? 'single',
                'phone'            => $request->phone,
                'emergency_contact'=> $request->emergency_contact,
                'address'          => $request->address,
                'basic_salary'     => $request->basic_salary ?? 0,
                'bank_name'        => $request->bank_name,
                'bank_account'     => $request->bank_account,
            ]);
            $newUser = $user;
        });

        if ($newUser) {
            \App\Services\MailConfigService::applyFromSettings();
            $school = \App\Models\SchoolSetting::current();
            try {
                $token = \Illuminate\Support\Facades\Password::broker()->createToken($newUser);
                $resetUrl = route('password.reset', ['token' => $token, 'email' => $newUser->email]);
                \Illuminate\Support\Facades\Mail::send('emails.staff-welcome', [
                    'newUser'  => $newUser,
                    'school'   => $school,
                    'resetUrl' => $resetUrl,
                ], function ($m) use ($newUser, $school) {
                    $m->to($newUser->email)
                      ->subject('Welcome to ' . ($school->school_name ?? 'the School') . ' — Set Your Password');
                });
            } catch (\Exception $e) {
                // Don't block staff creation if the email fails to send
            }
        }

        if ($newUser) {
            ActivityLogger::log('staff.create', 'Created staff account: ' . $newUser->name . ' (' . $newUser->email . ')', $newUser);
        }
        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member added successfully. A welcome email with login instructions has been sent.');
    }

    public function show(User $staff)
    {
        $staff->load(['role','staffProfile','staffDocuments']);
        return view('admin.staff.show', compact('staff'));
    }

    public function edit(User $staff)
    {
        $staff->load('staffProfile');
        $roles = Role::whereNotIn('slug',['super_admin','parent','student'])->get();
        return view('admin.staff.edit', compact('staff','roles'));
    }

    public function update(Request $request, User $staff)
    {
        if ($staff->hasRole('super_admin')) {
            return back()->with('error', 'The Super Admin account is protected and cannot be modified here.');
        }
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,'.$staff->id,
            'role_id'       => 'required|exists:roles,id',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function() use ($request, $staff) {
            $data = $request->only(['name','email','role_id','phone','is_active']);

            if ($request->hasFile('profile_photo')) {
                $data['profile_photo'] = $request->file('profile_photo')->store('staff/photos','public');
            }
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $staff->update($data);

            $staff->staffProfile()->updateOrCreate(
                ['user_id' => $staff->id],
                $request->only([
                    'designation','department','joining_date','qualification',
                    'experience','gender','dob','blood_group','marital_status',
                    'emergency_contact','address','basic_salary','bank_name','bank_account',
                ])
            );
        });
        ActivityLogger::log('staff.update', 'Updated staff account: ' . $staff->name, $staff);

        return redirect()->route('admin.staff.show', $staff)
            ->with('success', 'Staff updated successfully.');
    }

    public function destroy(User $staff)
    {
        if ($staff->hasRole('super_admin')) {
            return back()->with('error', 'The Super Admin account is protected and cannot be deleted.');
        }
        ActivityLogger::log('staff.delete', 'Deleted staff account: ' . $staff->name . ' (' . $staff->email . ')', $staff);
        $staff->delete();
        return redirect()->route('admin.staff.index')->with('success', 'Staff member removed.');
    }

    public function uploadDocument(Request $request, User $staff)
    {
        $request->validate([
            'document_type' => 'required|string|max:100',
            'document_file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $path = $request->file('document_file')->store('staff/documents','public');
        $staff->staffDocuments()->create([
            'document_type' => $request->document_type,
            'file_path'     => $path,
            'file_name'     => $request->file('document_file')->getClientOriginalName(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function deleteDocument(User $staff, StaffDocument $document)
    {
        $document->delete();
        return back()->with('success', 'Document removed.');
    }
}
