<?php $school = \App\Models\SchoolSetting::current(); ?>
<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — {{ $school->school_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --sidebar-bg:   {{ $school->sidebar_color ?? '#0f766e' }};
            --sidebar-dark: {{ \App\Helpers\ColorHelper::darken($school->sidebar_color ?? '#0f766e', 10) }};
            --accent:       {{ $school->accent_color ?? '#14b8a6' }};
        }
        [x-cloak] { display: none !important; }

        /* Sidebar */
        .sidebar       { background: var(--sidebar-bg); }
        .sidebar-border{ border-color: var(--sidebar-dark); }
        .sidebar-link  { display:flex; align-items:center; gap:0.75rem; padding:0.625rem 1rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; color:rgba(255,255,255,0.75); transition:all 0.15s; }
        .sidebar-link:hover { background:var(--sidebar-dark); color:#fff; }
        .sidebar-link.active{ background:var(--sidebar-dark); color:#fff; }

        /* Section headings */
        .sidebar-section          { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; padding:0 1rem; margin-bottom:0.25rem; margin-top:1rem; display:block; }
        .sidebar-section.s-main  { color:#fde68a; }
        .sidebar-section.s-academic { color:#86efac; }
        .sidebar-section.s-people   { color:#93c5fd; }
        .sidebar-section.s-finance  { color:#f9a8d4; }
        .sidebar-section.s-academics{ color:#c4b5fd; }
        .sidebar-section.s-system   { color:#fdba74; }

        /* Topbar accent */
        .topbar-border { border-bottom: 2px solid var(--accent); }

        /* Buttons */
        .btn-primary { background:var(--sidebar-bg); color:#fff; padding:0.5rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; transition:opacity 0.15s; border:none; cursor:pointer; }
        .btn-primary:hover { opacity:0.85; }

        /* Welcome banner */
        .welcome-banner { background: linear-gradient(135deg, var(--sidebar-bg), var(--accent)); }
    </style>
</head>
<body class="bg-gray-100 font-sans">

{{-- Mobile overlay --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
    class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"></div>

{{-- Sidebar --}}
<aside class="sidebar fixed top-0 left-0 h-full w-64 z-30 flex flex-col transition-transform duration-300 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    {{-- Logo area --}}
    <div class="flex items-center gap-3 px-5 py-4 sidebar-border" style="border-bottom-width:1px;border-bottom-style:solid;">
        @if($school->logo)
            <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 bg-white p-0.5">
        @else
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" style="color:var(--sidebar-bg)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
        @endif
        <div class="min-w-0">
            <p class="text-white font-bold text-sm leading-tight truncate">{{ $school->school_name }}</p>
            @if($school->motto)
                <p class="text-white/60 text-xs truncate">{{ $school->motto }}</p>
            @else
                <p class="text-white/60 text-xs">School Management</p>
            @endif
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-3">
        <p class="sidebar-section s-main">Main</p>
        <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">

        <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">

        {{-- MAIN --}}
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>

        {{-- ACADEMIC (admin + teacher only) --}}
        @if(auth()->user()->hasAnyRole(['super_admin','admin','teacher']))
        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-2">Academic</p>
        @if(auth()->user()->hasAnyRole(['super_admin','admin']))
        <a href="{{ route('admin.academic-years.index') }}" class="sidebar-link {{ request()->routeIs('admin.academic-years*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Academic Years
        </a>
        <a href="{{ route('admin.classes.index') }}" class="sidebar-link {{ request()->routeIs('admin.classes*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Classes & Sections
        </a>
        <a href="{{ route('admin.subjects.index') }}" class="sidebar-link {{ request()->routeIs('admin.subjects*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Subjects
        </a>
        @endif
        <a href="{{ route('admin.promotion.index') }}" class="sidebar-link {{ request()->routeIs('admin.promotion*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            Promote Students
        </a>
        <a href="{{ route('admin.timetable.index') }}" class="sidebar-link {{ request()->routeIs('admin.timetable*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Timetable
        </a>
        @endif

        {{-- PEOPLE --}}
        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-2">People</p>
        <a href="{{ route('admin.students.index') }}" class="sidebar-link {{ request()->routeIs('admin.students*') && !request()->routeIs('admin.students.pins*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Students
        </a>
        @if(auth()->user()->hasAnyRole(['super_admin','admin']))
        <a href="{{ route('admin.admissions.index') }}" class="sidebar-link {{ request()->routeIs('admin.admissions*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Admission Enquiries
            @if(($newEnquiriesCount ?? 0) > 0)
            <span class="ml-auto bg-red-500 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 min-w-[18px] text-center">{{ $newEnquiriesCount }}</span>
            @endif
        </a>
        @endif
        @if(auth()->user()->hasAnyRole(['super_admin','admin','receptionist']))
        <a href="{{ route('admin.students.pins.index') }}" class="sidebar-link {{ request()->routeIs('admin.students.pins*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            Student PINs
        </a>
        <a href="{{ route('admin.staff.index') }}" class="sidebar-link {{ request()->routeIs('admin.staff*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Staff
        </a>
        @endif

        {{-- FINANCE (admin + accountant only) --}}
        @if(auth()->user()->hasAnyRole(['super_admin','admin','accountant']))
        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-2">Finance</p>
        <a href="{{ route('admin.fees.collect') }}" class="sidebar-link {{ request()->routeIs('admin.fees.collect') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Collect Fees
        </a>
        <a href="{{ route('admin.fees.structures') }}" class="sidebar-link {{ request()->routeIs('admin.fees.structures*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Fee Structures
        </a>
        <a href="{{ route('admin.fees.report') }}" class="sidebar-link {{ request()->routeIs('admin.fees.report*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Collections
        </a>
        <a href="{{ route('admin.fees.balance') }}" class="sidebar-link {{ request()->routeIs('admin.fees.balance*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            Balances
        </a>
                <a href="{{ route('admin.finance.index') }}" class="sidebar-link {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            Accounts & Expenses
        </a>
        @endif

        {{-- ATTENDANCE (admin + teacher only) --}}
        @if(auth()->user()->hasAnyRole(['super_admin','admin','teacher']))
        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-2">Attendance</p>
        <a href="{{ route('admin.attendance.mark') }}" class="sidebar-link {{ request()->routeIs('admin.attendance.mark') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Mark Attendance
        </a>
        <a href="{{ route('admin.attendance.report') }}" class="sidebar-link {{ request()->routeIs('admin.attendance.report*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Monthly Report
        </a>

        {{-- EXAMS (admin + teacher only) --}}
        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-2">Exams</p>
        <a href="{{ route('admin.exams.index') }}" class="sidebar-link {{ request()->routeIs('admin.exams*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Exams & Marks
        </a>
        @if(auth()->user()->hasAnyRole(['super_admin','admin']))
        <a href="{{ route('admin.lesson-plans.index') }}" class="sidebar-link {{ request()->routeIs('admin.lesson-plans*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Lesson Plans
        </a>
        @endif
        @endif

        {{-- COMMUNICATION (admin only) --}}
        @if(auth()->user()->hasAnyRole(['super_admin','admin']))
        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-2">Communication</p>
        <a href="{{ route('admin.notices.index') }}" class="sidebar-link {{ request()->routeIs('admin.notices*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Notices
        </a>
        <a href="{{ route('admin.homework.index') }}" class="sidebar-link {{ request()->routeIs('admin.homework*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Homework
        </a>
        @endif

        {{-- SYSTEM --}}
        @if(auth()->user()->hasAnyRole(['super_admin','admin']))
        <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-2">System</p>
                <a href="{{ route('admin.website.index') }}" class="sidebar-link {{ request()->routeIs('admin.website*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
            Website
        </a>
<a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Settings
        </a>
        @endif
        @if(auth()->user()->hasRole('super_admin'))
        <a href="{{ route('admin.activity-logs.index') }}" class="sidebar-link {{ request()->routeIs('admin.activity-logs*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h3m-3-8h9M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Activity Log
        </a>
        @endif

        @if(auth()->user()->hasAnyRole(['super_admin','admin','accountant']))
        <a href="{{ route('admin.guide.show') }}" class="sidebar-link {{ request()->routeIs('admin.guide*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            User Guide
        </a>
        @endif
    </nav>

    {{-- User at bottom --}}
    <div class="px-4 py-4 sidebar-border" style="border-top-width:1px;border-top-style:solid;">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.profile.index') }}" class="flex items-center gap-3 flex-1 min-w-0 group">
                @if(auth()->user()->profile_photo)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                        class="w-9 h-9 rounded-full object-cover flex-shrink-0 border-2 border-white/30">
                @else
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                        style="background:var(--accent)">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate group-hover:underline">{{ auth()->user()->name }}</p>
                    <p class="text-white/60 text-xs truncate">{{ auth()->user()->role?->name }}</p>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="text-white/50 hover:text-white transition flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Main content --}}
<div class="lg:ml-64 min-h-screen flex flex-col">
    {{-- Topbar --}}
    <header class="bg-white topbar-border px-4 lg:px-6 py-4 flex items-center justify-between sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-4">
            <button @click="sidebarOpen=!sidebarOpen" class="lg:hidden text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <span class="hidden sm:inline">{{ now()->format('D, d M Y') }}</span>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                style="background:var(--sidebar-bg)">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success') || session('error'))
    <div class="px-4 lg:px-6 pt-4">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ session('error') }}
            </div>
        @endif
    </div>
    @endif

    {{-- Page content --}}
    <main class="flex-1 p-4 lg:p-6">
        @yield('content')
    </main>

    <footer class="text-center text-xs text-gray-400 py-4 border-t border-gray-100">
        © {{ date('Y') }} {{ $school->school_name }} — Powered by Drik Technologies
    </footer>
</div>

</body>
</html>
