<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change PIN</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>:root{--sidebar-bg:{{ $school->sidebar_color ?? '#7f1d1d' }};}</style>
@include('partials.favicon')
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
<div class="w-full max-w-sm">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-2">Set Your New PIN</h2>
        <p class="text-sm text-gray-500 mb-5">
            Welcome {{ $student->first_name }}! Please set a new 4-digit PIN before continuing.
        </p>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('student.change-pin.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Current PIN (given by school)</label>
                <input type="password" name="current_pin" required
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">New PIN (4 digits)</label>
                <input type="password" name="new_pin" required maxlength="4" pattern="\d{4}"
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                       placeholder="4 digits">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Confirm New PIN</label>
                <input type="password" name="new_pin_confirmation" required maxlength="4"
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2">
            </div>
            <button type="submit" class="w-full py-2.5 text-white rounded-lg text-sm font-medium" style="background:var(--sidebar-bg)">
                Set PIN & Continue
            </button>
        </form>
    </div>
</div>
</body>
</html>
