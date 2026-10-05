@extends('layouts.admin')
@section('title', 'Admit New Student')
@section('content')

<div class="mb-4">
    <a href="{{ route('admin.students.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Students
    </a>
</div>

<form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data" x-data="{ classId: '' }">
@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Main Form --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Admission Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full text-white text-xs flex items-center justify-center font-bold flex-shrink-0" style="background:var(--sidebar-bg)">1</span>
                Admission Details
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Admission No <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="admission_no" value="{{ old('admission_no') }}" placeholder="{{ $admissionNo }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 font-mono">
                    @error('admission_no')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Leave blank to auto-generate, or enter an existing ID from paper records.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Admission Date <span class="text-red-500">*</span></label>
                    <input type="date" name="admission_date" value="{{ old('admission_date', date('Y-m-d')) }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    @error('admission_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Academic Year</label>
                    <input type="text" value="{{ $academicYear?->name ?? 'Not set' }}" disabled
                        class="w-full px-3 py-2 border border-gray-100 rounded-lg text-sm bg-gray-50 text-gray-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Class <span class="text-red-500">*</span></label>
                    <select name="class_id" required x-model="classId"
                        @change="fetch('/admin/api/classes/' + classId + '/sections').then(r=>r.json()).then(data=>{ sections=data })"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="">Select class...</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div x-data="{ sections: [] }">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
                    <select name="section_id"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="">Select section...</option>
                        <template x-for="s in sections" :key="s.id">
                            <option :value="s.id" x-text="'Section ' + s.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Roll No</label>
                    <input type="number" name="roll_no" value="{{ old('roll_no') }}" min="1"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
        </div>

        {{-- Personal Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full text-white text-xs flex items-center justify-center font-bold flex-shrink-0" style="background:var(--sidebar-bg)">2</span>
                Personal Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('first_name') border-red-400 @enderror">
                    @error('first_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Other Name</label>
                    <input type="text" name="other_name" value="{{ old('other_name') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('last_name') border-red-400 @enderror">
                    @error('last_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="male" {{ old('gender')=='male'?'selected':'' }}>Male</option>
                        <option value="female" {{ old('gender')=='female'?'selected':'' }}>Female</option>
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
                            <option value="{{ $bg }}" {{ old('blood_group')==$bg?'selected':'' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Religion</label>
                    <select name="religion" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="">Select...</option>
                        @foreach(['Christianity','Islam','Traditional','Other'] as $r)
                            <option value="{{ $r }}" {{ old('religion')==$r?'selected':'' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality','Ghanaian') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Home Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
        </div>

        {{-- Previous School --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full text-white text-xs flex items-center justify-center font-bold flex-shrink-0" style="background:var(--sidebar-bg)">3</span>
                Previous School (Optional)
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Previous School Name</label>
                    <input type="text" name="previous_school" value="{{ old('previous_school') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Previous Class/Grade</label>
                    <input type="text" name="previous_class" value="{{ old('previous_class') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
        </div>

        {{-- Parent/Guardian --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full text-white text-xs flex items-center justify-center font-bold flex-shrink-0" style="background:var(--sidebar-bg)">4</span>
                Parent / Guardian
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="parent_name" value="{{ old('parent_name') }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('parent_name') border-red-400 @enderror">
                    @error('parent_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Relation <span class="text-red-500">*</span></label>
                    <select name="parent_relation" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="father" {{ old('parent_relation')=='father'?'selected':'' }}>Father</option>
                        <option value="mother" {{ old('parent_relation')=='mother'?'selected':'' }}>Mother</option>
                        <option value="guardian" {{ old('parent_relation')=='guardian'?'selected':'' }}>Guardian</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone <span class="text-red-500">*</span></label>
                    <input type="text" name="parent_phone" value="{{ old('parent_phone') }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('parent_phone') border-red-400 @enderror">
                    @error('parent_phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" name="parent_email" value="{{ old('parent_email') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Occupation</label>
                    <input type="text" name="parent_occupation" value="{{ old('parent_occupation') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT: Photo + Submit --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Student Photo</h3>
            <div class="flex flex-col items-center">
                <div class="w-32 h-32 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 overflow-hidden mb-3 flex items-center justify-center" id="photo-wrap">
                    <div id="photo-placeholder" class="text-center">
                        <svg class="w-10 h-10 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <p class="text-xs text-gray-400 mt-1">No photo</p>
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
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Summary</h3>
            <div class="text-xs text-gray-500 space-y-1">
                <p>• Admission No auto-generated unless you enter one</p>
                <p>• Academic Year: <strong>{{ $academicYear?->name ?? 'None set' }}</strong></p>
                <p>• Student login can be set up after admission</p>
            </div>
            <button type="submit" class="btn-primary w-full py-3 mt-4">
                Complete Admission
            </button>
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
