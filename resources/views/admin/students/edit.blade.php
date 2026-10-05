@extends('layouts.admin')
@section('title', 'Edit Student')
@section('content')

<div class="mb-4">
    <a href="{{ route('admin.students.show', $student) }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Profile
    </a>
</div>

@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
    <p class="font-medium mb-1">Please fix the following:</p>
    <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Admission Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Admission No</label>
                    @if(auth()->user()->isAdmin())
                    <input type="text" name="admission_no" value="{{ old('admission_no', $student->admission_no) }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 @error('admission_no') border-red-400 @enderror">
                    @error('admission_no')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    @else
                    <input type="text" value="{{ $student->admission_no }}" disabled
                        class="w-full px-3 py-2 border border-gray-100 rounded-lg text-sm bg-gray-50 text-gray-500 font-mono">
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Admission Date <span class="text-red-500">*</span></label>
                    <input type="date" name="admission_date" value="{{ old('admission_date', $student->admission_date->format('Y-m-d')) }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Class <span class="text-red-500">*</span></label>
                    <select name="class_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $student->class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
                    <select name="section_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="">No Section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" {{ $student->section_id == $section->id ? 'selected' : '' }}>Section {{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="is_active" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="1" {{ $student->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$student->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Personal Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Other Name</label>
                    <input type="text" name="other_name" value="{{ old('other_name', $student->other_name) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="male" {{ $student->gender=='male'?'selected':'' }}>Male</option>
                        <option value="female" {{ $student->gender=='female'?'selected':'' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $student->dob?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Blood Group</label>
                    <select name="blood_group" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="">Unknown</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}" {{ $student->blood_group==$bg?'selected':'' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <input type="text" name="address" value="{{ old('address', $student->address) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Photo</h3>
            <div class="flex flex-col items-center">
                @if($student->profile_photo)
                    <img src="{{ asset('storage/'.$student->profile_photo) }}" id="photo-preview"
                        class="w-28 h-28 rounded-xl object-cover mb-3 border border-gray-100">
                @else
                    <div class="w-28 h-28 rounded-xl flex items-center justify-center text-white text-3xl font-bold mb-3"
                        style="background:var(--sidebar-bg)" id="photo-preview-placeholder">
                        {{ strtoupper(substr($student->first_name,0,1)) }}
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
