<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $article->title }} — {{ $school->school_name }}</title>
<meta name="description" content="{{ $article->excerpt }}">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root { --primary: {{ $ws->get('primary_color','#0f766e') }}; --secondary: {{ $ws->get('secondary_color','#14b8a6') }}; }
* { font-family: 'Nunito', sans-serif; }
.text-primary { color: var(--primary); }
.bg-primary { background-color: var(--primary); }
.article-body p { margin-bottom: 1rem; line-height: 1.8; color: #374151; }
.article-body h2 { font-size: 1.4rem; font-weight: 800; margin: 1.5rem 0 0.5rem; color: #111827; }
.article-body h3 { font-size: 1.1rem; font-weight: 700; margin: 1rem 0 0.5rem; color: #1f2937; }
[x-cloak] { display: none !important; }
</style>
@include('partials.favicon')
</head>
<body class="bg-gray-50">

{{-- Navbar --}}
<nav class="fixed top-0 left-0 right-0 z-50 bg-white shadow-sm">
    <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            @if($school->logo && file_exists(public_path('storage/'.$school->logo)))
            <img src="/storage/{{ $school->logo }}" class="h-9 w-9 rounded-full object-cover">
            @endif
            <span class="font-extrabold text-primary">{{ $school->school_name }}</span>
        </a>
        <a href="{{ route('home') }}#news" class="text-sm text-gray-500 hover:text-primary font-semibold">← Back to News</a>
    </div>
</nav>

<main class="pt-24 pb-20">
    <div class="max-w-3xl mx-auto px-4">

        {{-- Category & date --}}
        <div class="flex items-center gap-3 mb-4">
            @if($article->category)
            <span class="text-xs font-bold text-primary bg-red-50 px-3 py-1 rounded-full">{{ $article->category }}</span>
            @endif
            <span class="text-xs text-gray-400">{{ $article->published_at?->format('d F Y') }}</span>
        </div>

        {{-- Title --}}
        <h1 class="text-3xl md:text-4xl font-black text-gray-900 leading-tight mb-4">{{ $article->title }}</h1>

        @if($article->excerpt)
        <p class="text-lg text-gray-500 mb-6 leading-relaxed border-l-4 pl-4" style="border-color:var(--primary)">
            {{ $article->excerpt }}
        </p>
        @endif

        {{-- Cover image --}}
        @if($article->image)
        <div class="rounded-2xl overflow-hidden mb-8 aspect-video">
            <img src="/storage/{{ $article->image }}" class="w-full h-full object-cover">
        </div>
        @endif

        {{-- Body --}}
        @if($article->body)
        <div class="article-body prose max-w-none">
            {!! nl2br(e($article->body)) !!}
        </div>
        @endif

        {{-- Related --}}
        @if($related->count())
        <div class="mt-16 border-t border-gray-200 pt-10">
            <h2 class="font-bold text-gray-700 text-lg mb-6">More News</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($related as $r)
                <a href="{{ route('website.news.show', $r->slug) }}"
                   class="group bg-white rounded-xl overflow-hidden border border-gray-100 hover:shadow-md transition-all">
                    @if($r->image)
                    <div class="aspect-video overflow-hidden">
                        <img src="/storage/{{ $r->image }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    @endif
                    <div class="p-4">
                        <p class="font-bold text-sm text-gray-800 group-hover:text-primary transition-colors leading-snug">{{ $r->title }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $r->published_at?->format('d M Y') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</main>

<footer class="py-8 text-center text-xs text-gray-400" style="background:var(--primary)">
    <p class="text-white/60">© {{ date('Y') }} {{ $school->school_name }}. All rights reserved.</p>
</footer>
@if(config('services.anthropic.api_key'))
@include('website.partials.chatbot')
@endif
</body>
</html>
