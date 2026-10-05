@php
    $school = null;
    try { $school = \App\Models\SchoolSetting::first(); } catch (\Throwable $e) {}
    $accent = $school->accent_color ?? '#14b8a6';
    $name   = $school->name ?? config('app.name', 'School Manager');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied — {{ $name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --accent: {{ $accent }}; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="max-w-md w-full text-center">
        @if($school?->logo)
            <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo" class="w-16 h-16 rounded-xl object-cover mx-auto mb-6 shadow-sm">
        @endif
        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6" style="background: color-mix(in srgb, var(--accent) 12%, white);">
            <svg class="w-8 h-8" style="color: var(--accent)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-xl font-semibold text-gray-800 mb-2">Access Denied</h1>
        <p class="text-sm text-gray-500 mb-8">{{ $exception->getMessage() ?: "You don't have permission to view this page." }}</p>
        <a href="{{ url()->previous() }}" class="inline-block px-5 py-2.5 rounded-lg text-sm font-medium text-white" style="background: var(--accent)">
            Go Back
        </a>
    </div>
</body>
</html>
