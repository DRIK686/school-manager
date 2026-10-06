@extends('layouts.admin')
@section('title', 'School Settings')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
@csrf
@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
    <p class="font-semibold mb-1">Settings were not saved:</p>
    <ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: School Info --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Basic Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-5 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                School Information
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">School Name <span class="text-red-500">*</span></label>
                    <input type="text" name="school_name" value="{{ old('school_name', $school->school_name) }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-offset-0 focus:border-transparent"
                        style="--tw-ring-color:var(--accent)">
                    @error('school_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">School Motto</label>
                    <input type="text" name="motto" value="{{ old('motto', $school->motto) }}" placeholder="e.g. Excellence in Education"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $school->phone) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $school->email) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Website</label>
                    <input type="text" name="website" value="{{ old('website', $school->website) }}" placeholder="https://"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Academic Year</label>
                    <input type="text" name="academic_year" value="{{ old('academic_year', $school->academic_year) }}" placeholder="e.g. 2024/2025"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <input type="text" name="address" value="{{ old('address', $school->address) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $school->currency_symbol) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Timezone</label>
                    <select name="timezone" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="Africa/Accra" {{ $school->timezone === 'Africa/Accra' ? 'selected' : '' }}>Africa/Accra (GMT+0)</option>
                        <option value="Africa/Lagos" {{ $school->timezone === 'Africa/Lagos' ? 'selected' : '' }}>Africa/Lagos (GMT+1)</option>
                        <option value="Africa/Nairobi" {{ $school->timezone === 'Africa/Nairobi' ? 'selected' : '' }}>Africa/Nairobi (GMT+3)</option>
                        <option value="UTC" {{ $school->timezone === 'UTC' ? 'selected' : '' }}>UTC</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Branding Colors --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-5 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                Branding & Colors
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                {{-- Sidebar Color --}}
                <div x-data="{ color: '{{ old('sidebar_color', $school->sidebar_color ?? '#0f766e') }}' }">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Sidebar Color</label>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input type="color" name="sidebar_color" x-model="color"
                                class="w-12 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                        </div>
                        <input type="text" x-model="color" maxlength="7"
                            class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2"
                            @input="if($event.target.value.match(/^#[0-9a-fA-F]{6}$/)) color=$event.target.value">
                    </div>
                    <div class="mt-2 h-8 rounded-lg transition-all duration-300" :style="'background:'+color"></div>
                    <p class="text-xs text-gray-400 mt-1">Main sidebar background</p>
                </div>

                {{-- Accent Color --}}
                <div x-data="{ color: '{{ old('accent_color', $school->accent_color ?? '#14b8a6') }}' }">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Accent Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="accent_color" x-model="color"
                            class="w-12 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                        <input type="text" x-model="color" maxlength="7"
                            class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2"
                            @input="if($event.target.value.match(/^#[0-9a-fA-F]{6}$/)) color=$event.target.value">
                    </div>
                    <div class="mt-2 h-8 rounded-lg transition-all duration-300" :style="'background:'+color"></div>
                    <p class="text-xs text-gray-400 mt-1">Topbar border & buttons</p>
                </div>

                {{-- Theme color --}}
                <div x-data="{ color: '{{ old('theme_color', $school->theme_color ?? '#0f766e') }}' }">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Theme Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="theme_color" x-model="color"
                            class="w-12 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                        <input type="text" x-model="color" maxlength="7"
                            class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2"
                            @input="if($event.target.value.match(/^#[0-9a-fA-F]{6}$/)) color=$event.target.value">
                    </div>
                    <div class="mt-2 h-8 rounded-lg transition-all duration-300" :style="'background:'+color"></div>
                    <p class="text-xs text-gray-400 mt-1">Welcome banner & cards</p>
                </div>
            </div>

            {{-- Color presets --}}
            <div class="mt-5">
                <p class="text-xs font-medium text-gray-600 mb-2">Quick Presets</p>
                <div class="flex flex-wrap gap-2" x-data
                    @preset.window="
                        document.querySelector('[name=sidebar_color]').value=$event.detail.sidebar;
                        document.querySelector('[name=accent_color]').value=$event.detail.accent;
                        document.querySelector('[name=theme_color]').value=$event.detail.theme;
                    ">
                    @foreach([
                        ['name'=>'Teal (Default)',  'sidebar'=>'#0f766e','accent'=>'#14b8a6','theme'=>'#0f766e'],
                        ['name'=>'Royal Blue',      'sidebar'=>'#1e40af','accent'=>'#3b82f6','theme'=>'#1d4ed8'],
                        ['name'=>'Deep Purple',     'sidebar'=>'#6b21a8','accent'=>'#a855f7','theme'=>'#7c3aed'],
                        ['name'=>'Forest Green',    'sidebar'=>'#14532d','accent'=>'#22c55e','theme'=>'#15803d'],
                        ['name'=>'Crimson',         'sidebar'=>'#991b1b','accent'=>'#ef4444','theme'=>'#b91c1c'],
                        ['name'=>'Slate',           'sidebar'=>'#1e293b','accent'=>'#64748b','theme'=>'#334155'],
                    ] as $preset)
                    <button type="button"
                        @click="$dispatch('preset', { sidebar: '{{ $preset['sidebar'] }}', accent: '{{ $preset['accent'] }}', theme: '{{ $preset['theme'] }}' })"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-gray-300 text-xs text-gray-600 hover:text-gray-800 transition bg-white hover:bg-gray-50">
                        <span class="flex gap-1">
                            <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $preset['sidebar'] }}"></span>
                            <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $preset['accent'] }}"></span>
                        </span>
                        {{ $preset['name'] }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Logo & Preview --}}
    <div class="space-y-6">

        {{-- Logo Upload --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-5 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                School Logo
            </h2>

            {{-- Current logo --}}
            <div class="flex flex-col items-center mb-4">
                <div class="w-24 h-24 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center bg-gray-50 overflow-hidden" id="logo-preview-wrap">
                    @if($school->logo)
                        <img src="{{ asset('storage/' . $school->logo) }}" id="logo-preview" class="w-full h-full object-contain p-1" alt="Logo">
                    @else
                        <div id="logo-placeholder" class="text-center">
                            <svg class="w-10 h-10 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-xs text-gray-400 mt-1">No logo</p>
                        </div>
                        <img id="logo-preview" class="w-full h-full object-contain p-1 hidden" alt="Logo">
                    @endif
                </div>
            </div>

            <label class="block">
                <span class="text-xs font-medium text-gray-600 mb-1 block">Upload New Logo</span>
                <input type="file" name="logo" accept="image/*" id="logo-input"
                    class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
            </label>
            <p class="text-xs text-gray-400 mt-2">PNG, JPG or SVG. Max 2MB. Recommended: square, at least 200×200px.</p>

            @error('logo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Live Sidebar Preview --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Live Preview</h2>
            <div class="rounded-xl overflow-hidden border border-gray-200 text-xs" style="height:220px"
                x-data="{ sb: '{{ $school->sidebar_color ?? '#0f766e' }}' }"
                @preset.window="sb=$event.detail.sidebar"
                @change.capture="
                    let sc = document.querySelector('[name=sidebar_color]');
                    if(sc) sb=sc.value;
                ">
                <div class="h-full flex">
                    {{-- Mini sidebar --}}
                    <div class="w-28 h-full flex flex-col p-2 gap-1" :style="'background:'+sb">
                        <div class="flex items-center gap-1 mb-1 p-1">
                            <div class="w-5 h-5 bg-white rounded flex-shrink-0"></div>
                            <div class="flex-1">
                                <div class="h-1.5 bg-white/70 rounded w-full mb-0.5"></div>
                                <div class="h-1 bg-white/40 rounded w-3/4"></div>
                            </div>
                        </div>
                        <div class="h-2 rounded" style="background:rgba(255,255,255,0.2)"></div>
                        <div class="flex items-center gap-1 rounded px-1 py-0.5" style="background:rgba(0,0,0,0.15)">
                            <div class="w-2 h-2 rounded-sm bg-white/60"></div>
                            <div class="h-1.5 bg-white/80 rounded flex-1"></div>
                        </div>
                        <div class="h-2 rounded mt-1" style="background:rgba(255,255,255,0.15)"></div>
                        <div class="flex items-center gap-1 rounded px-1 py-0.5">
                            <div class="w-2 h-2 rounded-sm bg-white/40"></div>
                            <div class="h-1.5 bg-white/50 rounded flex-1"></div>
                        </div>
                        <div class="flex items-center gap-1 rounded px-1 py-0.5">
                            <div class="w-2 h-2 rounded-sm bg-white/40"></div>
                            <div class="h-1.5 bg-white/50 rounded flex-1"></div>
                        </div>
                    </div>
                    {{-- Mini content --}}
                    <div class="flex-1 bg-gray-50 flex flex-col">
                        <div class="bg-white border-b-2 border-gray-200 p-2 flex items-center justify-between">
                            <div class="h-2 w-16 bg-gray-200 rounded"></div>
                            <div class="w-4 h-4 rounded-full bg-gray-200"></div>
                        </div>
                        <div class="p-2 flex-1">
                            <div class="rounded-lg p-2 mb-2 text-white text-xs font-medium" :style="'background:'+sb">
                                <div class="h-2 w-20 bg-white/80 rounded mb-1"></div>
                                <div class="h-1.5 w-28 bg-white/50 rounded"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <div class="bg-white rounded p-1.5 border border-gray-100">
                                    <div class="h-1.5 w-10 bg-gray-200 rounded mb-1"></div>
                                    <div class="h-3 w-6 bg-gray-300 rounded"></div>
                                </div>
                                <div class="bg-white rounded p-1.5 border border-gray-100">
                                    <div class="h-1.5 w-10 bg-gray-200 rounded mb-1"></div>
                                    <div class="h-3 w-6 bg-gray-300 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        @if(auth()->user()->hasRole('super_admin'))
        {{-- SMTP / Email Settings --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
            <h2 class="font-semibold text-gray-800 border-b border-gray-100 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Email (SMTP) Settings
            </h2>

            {{-- Test result banners --}}
            @if(session('smtp_success'))
            <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 flex items-start gap-3">
                <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-green-700">✅ SMTP Test Successful!</p>
                    <p class="text-xs text-green-600 mt-0.5">{{ session('smtp_success') }}</p>
                </div>
            </div>
            @endif

            @if(session('smtp_error'))
            <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-700">❌ SMTP Test Failed</p>
                    <p class="text-xs text-red-600 mt-1 font-mono bg-red-100 px-2 py-1 rounded">{{ session('smtp_error') }}</p>
                    <p class="text-xs text-red-500 mt-1">Check your host, port, credentials and encryption settings.</p>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ $school->mail_host }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                           placeholder="e.g. smtp.zoho.com / smtp.gmail.com">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">SMTP Port</label>
                    <input type="number" name="mail_port" value="{{ $school->mail_port ?? 587 }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                           placeholder="587">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">SMTP Username</label>
                    <input type="text" name="mail_username" value="{{ $school->mail_username }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                           placeholder="your@email.com">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">SMTP Password</label>
                    <input type="password" name="mail_password"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                           placeholder="{{ $school->mail_password ? '••••••••• (leave blank to keep current)' : 'App password or SMTP password' }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Encryption</label>
                    <select name="mail_encryption"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
                        <option value="tls" {{ ($school->mail_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (port 587) — recommended</option>
                        <option value="ssl" {{ ($school->mail_encryption ?? '') === 'ssl'  ? 'selected' : '' }}>SSL (port 465)</option>
                        <option value=""    {{ ($school->mail_encryption ?? '') === ''     ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ $school->mail_from_address }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                           placeholder="noreply@yourschool.com">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ $school->mail_from_name ?? $school->school_name }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                           placeholder="e.g. Sunrise Academy">
                </div>
            </div>

            {{-- Test email section --}}
            <div class="border border-dashed border-gray-200 rounded-xl p-4 bg-gray-50 space-y-3">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Test SMTP Connection</p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Send test email to</label>
                    <input type="email" name="test_email_to"
                           value="{{ $school->mail_username }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 bg-white"
                           placeholder="recipient@example.com">
                    <p class="text-xs text-gray-400 mt-1">
                        Settings are saved first, then a test email is sent to this address.
                    </p>
                </div>
                <button type="submit" name="test_email" value="1"
                        class="flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white rounded-lg text-sm text-gray-700 hover:bg-gray-100 transition font-medium">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Save & Send Test Email
                </button>
            </div>
        </div>
        @endif

        {{-- Save button --}}
        <button type="submit" class="btn-primary w-full py-3 text-center">
            Save All Settings
        </button>
    </div>

</div>
</form>

<script>
// Logo preview
document.getElementById('logo-input')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        const img = document.getElementById('logo-preview');
        const placeholder = document.getElementById('logo-placeholder');
        img.src = ev.target.result;
        img.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(file);
});
</script>
@endsection
