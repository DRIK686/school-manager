@extends('layouts.admin')
@section('title', 'Add Staff Member')
@section('content')

<div class="mb-4">
    <a href="{{ route('admin.staff.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Staff
    </a>
</div>

<form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data">
@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        {{-- Account Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full text-white text-xs flex items-center justify-center font-bold" style="background:var(--sidebar-bg)">1</span>
                Account & Role
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="">Select role...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Employee ID</label>
                    <input type="text" value="{{ $employeeId }}" disabled
                        class="w-full px-3 py-2 border border-gray-100 rounded-lg text-sm bg-gray-50 text-gray-500 font-mono">
                </div>
            </div>
        </div>

        {{-- Employment Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full text-white text-xs flex items-center justify-center font-bold" style="background:var(--sidebar-bg)">2</span>
                Employment Details
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Designation</label>
                    <input type="text" name="designation" value="{{ old('designation') }}" placeholder="e.g. Class Teacher"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Department</label>
                    <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Sciences"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Joining Date <span class="text-red-500">*</span></label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', date('Y-m-d')) }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Qualification</label>
                    <input type="text" name="qualification" value="{{ old('qualification') }}" placeholder="e.g. B.Ed, M.Sc"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Experience</label>
                    <input type="text" name="experience" value="{{ old('experience') }}" placeholder="e.g. 5 years"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Basic Salary ({{ \App\Models\SchoolSetting::current()->currency_symbol }})</label>
                    <input type="number" name="basic_salary" value="{{ old('basic_salary',0) }}" min="0" step="0.01"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
        </div>

        {{-- Personal Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full text-white text-xs flex items-center justify-center font-bold" style="background:var(--sidebar-bg)">3</span>
                Personal Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Blood Group</label>
                    <select name="blood_group" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="">Unknown</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}">{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Marital Status</label>
                    <select name="marital_status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="divorced">Divorced</option>
                        <option value="widowed">Widowed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Emergency Contact</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
        </div>

        {{-- Bank Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full text-white text-xs flex items-center justify-center font-bold" style="background:var(--sidebar-bg)">4</span>
                Bank Details (Optional)
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Account Number</label>
                    <input type="text" name="bank_account" value="{{ old('bank_account') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT: Photo + Submit --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Staff Photo</h3>
            <div class="flex flex-col items-center">
                <div class="w-32 h-32 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 overflow-hidden mb-3 flex items-center justify-center">
                    <div id="photo-placeholder" class="text-center">
                        <svg class="w-10 h-10 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <img id="photo-preview" class="w-full h-full object-cover hidden" alt="">
                </div>
                <label class="cursor-pointer text-xs px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-600">
                    Upload Photo
                    <input type="file" name="profile_photo" id="photo-input" accept="image/*" class="hidden">
                </label>
                <p class="text-xs text-gray-400 mt-1">JPG, PNG. Max 2MB.</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-xs text-gray-500 mb-4">Staff member will be able to log in with the email and password you set.</p>
            <button type="submit" class="btn-primary w-full py-3">Add Staff Member</button>
        </div>
    </div>
</div>
</form>

<script>
document.getElementById('photo-input')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        const img = document.getElementById('photo-preview');
        const ph = document.getElementById('photo-placeholder');
        img.src = ev.target.result;
        img.classList.remove('hidden');
        if (ph) ph.classList.add('hidden');
    };
    reader.readAsDataURL(file);
});
</script>
@endsection
