<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Teacher Portal') — {{ $school->school_name ?? 'SchoolManager' }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
:root {
  --sidebar-bg: {{ $school->sidebar_color ?? '#0f766e' }};
  --accent:     {{ $school->accent_color  ?? '#b91c1c' }};
}
.sidebar-bg  { background-color: var(--sidebar-bg); }
.accent-bg   { background-color: var(--accent); }
.accent-text { color: var(--accent); }
.sidebar-link {
    display:flex; align-items:center; gap:10px;
    padding:9px 16px; border-radius:8px; font-size:14px;
    color:rgba(255,255,255,0.75); transition:all .15s;
}
.sidebar-link:hover, .sidebar-link.active {
    background:rgba(255,255,255,0.15); color:#fff;
}
</style>
@include('partials.favicon')
</head>
<body class="bg-gray-50 text-gray-800" x-data="{ sidebarOpen: false }">

<!-- Mobile overlay -->
<div x-show="sidebarOpen" @click="sidebarOpen=false"
     class="fixed inset-0 bg-black/40 z-20 lg:hidden" x-cloak></div>

<!-- Sidebar -->
<aside class="sidebar-bg fixed top-0 left-0 h-full w-60 z-30 flex flex-col transition-transform duration-200
              lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
        @if(($school->logo ?? null) && file_exists(public_path('storage/'.$school->logo)))
            <img src="/storage/{{ $school->logo }}" class="h-9 w-9 rounded-full object-cover">
        @else
            <div class="h-9 w-9 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">
                {{ strtoupper(substr($school->school_name ?? 'S', 0, 1)) }}
            </div>
        @endif
        <div>
            <div class="text-white font-semibold text-sm leading-tight">{{ $school->school_name ?? 'SchoolManager' }}</div>
            <div class="text-white/50 text-xs">Teacher Portal</div>
        </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 mb-2">Main</p>

        <a href="{{ route('teacher.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 mt-4 mb-2">Teaching</p>

        <a href="{{ route('teacher.my-classes') }}"
           class="sidebar-link {{ request()->routeIs('teacher.my-classes') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            My Classes
        </a>

        <a href="{{ route('teacher.my-students') }}"
           class="sidebar-link {{ request()->routeIs('teacher.my-students') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            My Students
        </a>

        <a href="{{ route('teacher.promotion.index') }}"
           class="sidebar-link {{ request()->routeIs('teacher.promotion*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
            Promotion Recommendations
        </a>

                        <a href="{{ route('teacher.homework.index') }}"
           class="sidebar-link {{ request()->routeIs('teacher.homework*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Homework
        </a>
<a href="{{ route('admin.timetable.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.timetable*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Timetable
        </a>
<a href="{{ route('teacher.lesson-plans.index') }}"
           class="sidebar-link {{ request()->routeIs('teacher.lesson-plans*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Lesson Plans
        </a>

        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 mt-4 mb-2">Exams</p>

        <a href="{{ route('admin.exams.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.exams*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Exams & Marks
        </a>

        <a href="{{ route('admin.attendance.mark') }}"
           class="sidebar-link {{ request()->routeIs('admin.attendance*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Attendance
        </a>
        <a href="{{ route('teacher.profile.index') }}"
           class="sidebar-link {{ request()->routeIs('teacher.profile*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            My Profile
        </a>
    </nav>

    <!-- User -->
    <div class="px-4 py-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-full accent-bg flex items-center justify-center text-white text-xs font-bold shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</div>
                <div class="text-white/50 text-xs">Teacher</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="text-white/50 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Main -->
<div class="lg:ml-60 min-h-screen flex flex-col">
    <!-- Topbar -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-4 sticky top-0 z-10">
        <button @click="sidebarOpen=!sidebarOpen" class="lg:hidden text-gray-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="flex-1">
            <h1 class="text-base font-semibold text-gray-800">@yield('page-title', 'Teacher Portal')</h1>
        </div>
        <span class="text-xs text-gray-400">{{ now()->format('D, d M Y') }}</span>
    </header>

    <!-- Flash -->
    <div class="px-6 pt-4">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-2">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-2">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <main class="flex-1 p-6">
        @yield('content')
    </main>

    <footer class="text-center text-xs text-gray-400 py-4 border-t border-gray-100">
        © {{ date('Y') }} {{ $school->school_name ?? 'SchoolManager' }} — Powered by Drik Technologies
    </footer>
</div>
</body>
</html>
