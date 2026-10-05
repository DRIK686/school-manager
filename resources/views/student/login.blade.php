<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Login — {{ $school->school_name ?? 'SchoolManager' }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>:root{--sidebar-bg:{{ $school->sidebar_color ?? '#7f1d1d' }};}</style>
@include('partials.favicon')
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        @if(($school->logo??null)&&file_exists(public_path('storage/'.$school->logo)))
            <img src="/storage/{{ $school->logo }}" class="h-16 w-16 rounded-full object-cover mx-auto mb-3">
        @else
            <div class="h-16 w-16 rounded-full mx-auto mb-3 flex items-center justify-center text-white text-2xl font-bold"
                 style="background:var(--sidebar-bg)">
                {{ strtoupper(substr($school->school_name??'S',0,1)) }}
            </div>
        @endif
        <h1 class="text-xl font-bold text-gray-800">{{ $school->school_name ?? 'SchoolManager' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Student Portal</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-5">Sign in to your portal</h2>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('student.login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Admission Number</label>
                <input type="text" name="admission_no" value="{{ old('admission_no') }}" required autofocus
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                       placeholder="e.g. 2026/0001">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">PIN</label>
                <input type="password" name="pin" required
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                       placeholder="Enter your PIN">
            </div>
            <button type="submit"
                    class="w-full py-2.5 text-white rounded-lg text-sm font-medium mt-2"
                    style="background:var(--sidebar-bg)">
                Sign In
            </button>
        </form>

        <p class="text-xs text-gray-400 text-center mt-4">
            Don't know your PIN? Ask your class teacher or the school office.
        </p>
    </div>
</div>
</body>
</html>
