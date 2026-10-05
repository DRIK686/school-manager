@extends('layouts.admin')
@section('title', 'Edit Staff')
@section('content')

<div class="mb-4">
    <a href="{{ route('admin.staff.show', $staff) }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Profile
    </a>
</div>

<form method="POST" action="{{ route('admin.staff.update', $staff) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Account & Role</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name',$staff->name) }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email',$staff->email) }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">New Password <span class="text-gray-400">(leave blank to keep)</span></label>
                    <input type="password" name="password"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ $staff->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="is_active" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="1" {{ $staff->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$staff->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Employment Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Designation</label>
                    <input type="text" name="designation" value="{{ old('designation',$staff->staffProfile?->designation) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Department</label>
                    <input type="text" name="department" value="{{ old('department',$staff->staffProfile?->department) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date',$staff->staffProfile?->joining_date?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Basic Salary</label>
                    <input type="number" name="basic_salary" value="{{ old('basic_salary',$staff->staffProfile?->basic_salary) }}" min="0" step="0.01"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone',$staff->phone) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <input type="text" name="address" value="{{ old('address',$staff->staffProfile?->address) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Photo</h3>
            <div class="flex flex-col items-center">
                @if($staff->profile_photo)
                    <img src="{{ asset('storage/'.$staff->profile_photo) }}" id="photo-preview"
                        class="w-28 h-28 rounded-xl object-cover mb-3 border border-gray-100">
                @else
                    <div class="w-28 h-28 rounded-xl flex items-center justify-center text-white text-3xl font-bold mb-3"
                        style="background:var(--sidebar-bg)" id="photo-preview-placeholder">
                        {{ strtoupper(substr($staff->name,0,1)) }}
                    </div>
                    <img id="photo-preview" class="w-28 h-28 rounded-xl object-cover mb-3 hidden" alt="">
                @endif
                <label class="cursor-pointer text-xs px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-600">
                    Change Photo
                    <input type="file" name="profile_photo" id="photo-input" accept="image/*" class="hidden">
                </label>
            </div>
        </div>
        <button type="submit" class="btn-primary w-full py-3">Save Changes</button>
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
        img.src = ev.target.result;
        img.classList.remove('hidden');
        document.getElementById('photo-preview-placeholder')?.classList.add('hidden');
    };
    reader.readAsDataURL(file);
});
</script>
@endsection
