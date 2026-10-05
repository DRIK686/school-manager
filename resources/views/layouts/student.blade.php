<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title','Student Portal') — {{ $school->school_name ?? 'SchoolManager' }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
:root { --sidebar-bg: {{ $school->sidebar_color ?? '#7f1d1d' }}; --accent: {{ $school->accent_color ?? '#b91c1c' }}; }
.sidebar-bg { background-color: var(--sidebar-bg); }
.sidebar-link { display:flex;align-items:center;gap:10px;padding:9px 16px;border-radius:8px;font-size:14px;color:rgba(255,255,255,0.75);transition:all .15s; }
.sidebar-link:hover,.sidebar-link.active { background:rgba(255,255,255,0.15);color:#fff; }
</style>
@include('partials.favicon')
</head>
<body class="bg-gray-50" x-data="{open:false}">
<div x-show="open" @click="open=false" class="fixed inset-0 bg-black/40 z-20 lg:hidden" x-cloak></div>

<aside class="sidebar-bg fixed top-0 left-0 h-full w-60 z-30 flex flex-col lg:translate-x-0 transition-transform duration-200"
       :class="open?'translate-x-0':'-translate-x-full lg:translate-x-0'">
    <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
        @if(($school->logo??null)&&file_exists(public_path('storage/'.$school->logo)))
            <img src="/storage/{{ $school->logo }}" class="h-9 w-9 rounded-full object-cover">
        @else
            <div class="h-9 w-9 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">
                {{ strtoupper(substr($school->school_name??'S',0,1)) }}
            </div>
        @endif
        <div>
            <div class="text-white font-semibold text-sm">{{ $school->school_name??'SchoolManager' }}</div>
            <div class="text-white/50 text-xs">Student Portal</div>
        </div>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 mb-2">Menu</p>
        @php
        $links = [
            ['route'=>'student.dashboard', 'label'=>'Dashboard', 'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route'=>'student.profile',    'label'=>'My Profile', 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['route'=>'student.fees',      'label'=>'My Fees',   'icon'=>'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
            ['route'=>'student.attendance','label'=>'Attendance', 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ['route'=>'student.results',   'label'=>'Results',   'icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['route'=>'student.homework',  'label'=>'Homework',  'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['route'=>'student.notices',   'label'=>'Notices',   'icon'=>'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['route'=>'student.timetable', 'label'=>'Timetable', 'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['route'=>'student.exam-timetable', 'label'=>'Exam Timetable', 'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ];
        @endphp
        @foreach($links as $link)
        <a href="{{ route($link['route']) }}"
           class="sidebar-link {{ request()->routeIs($link['route']) ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
            </svg>
            {{ $link['label'] }}
        </a>
        @endforeach
        <a href="{{ route('student.exams-practice') }}" target="_blank"
           class="sidebar-link">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Exams Practice
            <svg class="w-3 h-3 shrink-0 ml-auto opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
    </nav>
    <div class="px-4 py-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-full bg-white/20 flex items-center justify-center text-white text-xs font-bold shrink-0">
                {{ strtoupper(substr($student->first_name,0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-white text-sm font-medium truncate">{{ $student->full_name }}</div>
                <div class="text-white/50 text-xs">{{ $student->admission_no }}</div>
            </div>
            <form method="POST" action="{{ route('student.logout') }}">@csrf
                <button type="submit" class="text-white/50 hover:text-white" title="Logout">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="lg:ml-60 min-h-screen flex flex-col">
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-4 sticky top-0 z-10">
        <button @click="open=!open" class="lg:hidden text-gray-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="flex-1">
            <h1 class="text-base font-semibold text-gray-800">@yield('page-title','Student Portal')</h1>
        </div>
        <a href="{{ route('student.change-pin') }}" class="text-xs text-gray-400 hover:text-gray-600">Change PIN</a>
        <span class="text-xs text-gray-400">{{ now()->format('D, d M Y') }}</span>
    </header>
    <div class="px-6 pt-4">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-2">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-2">{{ session('error') }}</div>
        @endif
    </div>
    <main class="flex-1 p-6">@yield('content')</main>
    <footer class="text-center text-xs text-gray-400 py-4 border-t border-gray-100">
        © {{ date('Y') }} {{ $school->school_name??'SchoolManager' }} — Powered by Drik Technologies
    </footer>
</div>
</body>
</html>
