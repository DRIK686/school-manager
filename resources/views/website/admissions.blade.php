<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admissions — {{ $school->school_name }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
* { font-family: 'Nunito', sans-serif; }
:root { --primary: {{ $ws->get('primary_color','#7f1d1d') }}; }
.bg-primary { background-color: var(--primary); }
.text-primary { color: var(--primary); }
.btn-primary { background-color: var(--primary); color: white; padding: 12px 28px; border-radius: 50px; font-weight: 700; display: inline-block; }
[x-cloak] { display: none !important; }
</style>
</head>
<body class="bg-gray-50">
<nav class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
    <a href="{{ route('home') }}" class="flex items-center gap-3">
        @if($school->logo && file_exists(public_path('storage/'.$school->logo)))
        <img src="/storage/{{ $school->logo }}" class="h-9 w-9 rounded-full object-cover">
        @endif
        <span class="font-extrabold text-primary">{{ $school->school_name }}</span>
    </a>
    <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Home</a>
</nav>

<div class="max-w-2xl mx-auto px-4 py-16">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-black text-gray-900 mb-3">Apply for Admission</h1>
        <p class="text-gray-500">Fill in the form below and we will get back to you within 24 hours.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl mb-6 text-center">
        <p class="font-bold text-lg">✅ Application Received!</p>
        <p class="text-sm mt-1">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <form method="POST" action="{{ route('website.admissions.submit') }}" class="space-y-5">
            @csrf
            {{-- Honeypot: hidden from real users, bots often fill every field --}}
            <div style="position:absolute; left:-9999px;" aria-hidden="true">
                <label for="website_field">Leave this field blank</label>
                <input type="text" name="website_field" id="website_field" tabindex="-1" autocomplete="off">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Child's Full Name *</label>
                <input type="text" name="child_name" required value="{{ old('child_name') }}"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary"
                       placeholder="Enter child's full name">
                @error('child_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Date of Birth *</label>
                    <input type="date" name="dob" required value="{{ old('dob') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary">
                    @error('dob')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Applying for Grade *</label>
                    <select name="grade" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary">
                        <option value="">Select grade...</option>
                        @foreach(['Nursery','KG 1','KG 2','Basic 1','Basic 2','Basic 3','Basic 4','Basic 5','Basic 6','JHS 1','JHS 2','JHS 3'] as $g)
                        <option value="{{ $g }}" {{ old('grade')===$g?'selected':'' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Parent/Guardian Full Name *</label>
                <input type="text" name="parent_name" required value="{{ old('parent_name') }}"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary"
                       placeholder="Parent or guardian name">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number *</label>
                    <input type="tel" name="parent_phone" required value="{{ old('parent_phone') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary"
                           placeholder="0XX XXX XXXX">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="parent_email" value="{{ old('parent_email') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary"
                           placeholder="Optional">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Additional Message</label>
                <textarea name="message" rows="3"
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary"
                          placeholder="Any questions or special requirements?">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="btn-primary w-full text-center text-base">
                Submit Application
            </button>
            <p class="text-xs text-gray-400 text-center">
                By submitting, you agree to be contacted by {{ $school->school_name }} regarding your application.
            </p>
        </form>
    </div>
</div>
@include('website.partials.chatbot')
</body>
</html>
