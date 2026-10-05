<div class="max-w-4xl" x-data="{ tab: '{{ session('tab', 'profile') }}' }">

    {{-- Profile Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex items-center gap-5">
            <div class="relative flex-shrink-0">
                @if($user->profile_photo)
                    <img src="{{ asset('storage/' . $user->profile_photo) }}"
                        class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-md" alt="Photo">
                @else
                    <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-md"
                        style="background:var(--sidebar-bg)">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <span class="absolute bottom-0 right-0 w-5 h-5 bg-green-400 border-2 border-white rounded-full"></span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->role?->name }}</p>
                <p class="text-sm text-gray-400">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 bg-white rounded-xl p-1.5 shadow-sm border border-gray-100 w-fit">
        <button @click="tab='profile'"
            :class="tab==='profile' ? 'text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-150"
            :style="tab==='profile' ? 'background:var(--sidebar-bg)' : ''">
            Edit Profile
        </button>
        <button @click="tab='password'"
            :class="tab==='password' ? 'text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-150"
            :style="tab==='password' ? 'background:var(--sidebar-bg)' : ''">
            Change Password
        </button>
    </div>

    {{-- Edit Profile Tab --}}
    <div x-show="tab==='profile'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-5">Personal Information</h3>
            <form method="POST" action="{{ route($routePrefix . '.profile.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    {{-- Profile photo upload --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-2">Profile Photo</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200 flex-shrink-0 bg-gray-50" id="photo-preview-wrap">
                                @if($user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" id="photo-preview" class="w-full h-full object-cover" alt="">
                                @else
                                    <div id="photo-placeholder" class="w-full h-full flex items-center justify-center text-white text-xl font-bold"
                                        style="background:var(--sidebar-bg)">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <img id="photo-preview" class="w-full h-full object-cover hidden" alt="">
                                @endif
                            </div>
                            <div>
                                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Upload Photo
                                    <input type="file" name="profile_photo" id="photo-input" accept="image/*" class="hidden">
                                </label>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG. Max 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('name') border-red-400 @enderror">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                        <input type="text" value="{{ $user->role?->name }}" disabled
                            class="w-full px-3 py-2.5 border border-gray-100 rounded-lg text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
                    </div>

                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn-primary px-8 py-2.5">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Change Password Tab --}}
    <div x-show="tab==='password'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-5">Change Password</h3>
            <form method="POST" action="{{ route($routePrefix . '.profile.password') }}" class="max-w-md">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Current Password <span class="text-red-500">*</span></label>
                        <input type="password" name="current_password" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('current_password') border-red-400 @enderror">
                        @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 @error('password') border-red-400 @enderror">
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        <p class="text-xs text-gray-400 mt-1">Minimum 8 characters, must include letters and numbers.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Confirm New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn-primary px-8 py-2.5">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
document.getElementById('photo-input')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        const img = document.getElementById('photo-preview');
        const placeholder = document.getElementById('photo-placeholder');
        img.src = ev.target.result;
        img.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(file);
});
</script>
