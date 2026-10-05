<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — {{ $school->school_name ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: {{ $school->theme_color ?? '#0f766e' }};
            --primary-dark: {{ $school->sidebar_color ?? '#0d6460' }};
            --accent: {{ $school->accent_color ?? '#14b8a6' }};
        }
        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .ring-primary:focus { --tw-ring-color: var(--primary); border-color: var(--primary); }
        .btn-primary { background-color: var(--primary); }
        .btn-primary:hover { background-color: var(--primary-dark); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4"
      style="background: linear-gradient(135deg, {{ $school->theme_color ?? '#0f766e' }}15 0%, {{ $school->accent_color ?? '#14b8a6' }}15 100%)">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-primary px-8 py-10 text-center">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 overflow-hidden">
                    @if($school && $school->logo && file_exists(public_path('storage/'.$school->logo)))
                    <img src="/storage/{{ $school->logo }}" class="w-full h-full object-cover">
                    @else
                    <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-white">Reset Password</h1>
                <p class="text-white/70 text-sm mt-1">Enter your email to receive a reset link</p>
            </div>

            <div class="px-8 py-8">
                @if(session('status'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                        {{ session('status') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 ring-primary outline-none transition text-sm">
                    </div>

                    <button type="submit"
                        class="w-full btn-primary text-white font-semibold py-2.5 px-4 rounded-lg transition duration-150 text-sm">
                        Send Reset Link
                    </button>

                    <a href="{{ route('login') }}" class="block text-center text-sm text-gray-500 hover:text-gray-700">
                        ← Back to Sign In
                    </a>
                </form>
            </div>
        </div>
        <p class="text-center text-xs text-gray-500 mt-4">© {{ date('Y') }} {{ $school->school_name ?? config('app.name', 'SchoolManager') }}. All rights reserved.</p>
    </div>
</body>
</html>
