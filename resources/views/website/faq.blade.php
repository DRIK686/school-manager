<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FAQ — {{ $school->school_name }}</title>
<script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-gray-50" x-data="{ open: null }">
<nav class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
    <a href="{{ route('home') }}" class="flex items-center gap-3">
        @if($school->logo && file_exists(public_path('storage/'.$school->logo)))
        <img src="/storage/{{ $school->logo }}" class="h-9 w-9 rounded-full object-cover">
        @endif
        <span class="font-extrabold text-primary">{{ $school->school_name }}</span>
    </a>
    <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Home</a>
</nav>

<div class="max-w-3xl mx-auto px-4 py-16">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-black text-gray-900 mb-3">{{ $ws->get('faq_headline','Frequently Asked Questions') }}</h1>
        @if($ws->get('faq_subtext'))
        <p class="text-gray-500">{{ $ws->get('faq_subtext') }}</p>
        @endif
    </div>

    @if($faqs->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center text-gray-400">
        No questions have been added yet. Check back soon.
    </div>
    @else
    <div class="space-y-3">
        @foreach($faqs as $i => $faq)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <button @click="open = (open === {{ $i }} ? null : {{ $i }})"
                    class="w-full text-left px-6 py-5 flex items-center justify-between gap-4">
                <span class="font-bold text-gray-900">{{ $faq->title }}</span>
                <svg class="w-5 h-5 text-primary flex-shrink-0 transition-transform" :class="open === {{ $i }} ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open === {{ $i }}" x-cloak x-collapse class="px-6 pb-5 text-gray-600 leading-relaxed">
                {{ $faq->description }}
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="text-center mt-12">
        <p class="text-gray-500 mb-4">Still have a question?</p>
        <a href="{{ route('website.admissions') }}" class="btn-primary text-sm">Contact Admissions</a>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@if(config('services.anthropic.api_key'))
@include('website.partials.chatbot')
@endif
</body>
</html>
