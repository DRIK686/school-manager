<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $ws->get('hero_headline', $school->school_name) }}</title>
<meta name="description" content="{{ $ws->get('hero_subtext') }}">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
:root {
@php
    $hex = ltrim($ws->get('primary_color', '#0f766e'), '#');
    if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
    $primaryDeep = preg_match('/^[0-9a-fA-F]{6}$/', $hex)
        ? sprintf('#%02x%02x%02x',
            (int) round(hexdec(substr($hex, 0, 2)) * 0.35),
            (int) round(hexdec(substr($hex, 2, 2)) * 0.35),
            (int) round(hexdec(substr($hex, 4, 2)) * 0.35))
        : '#052927';
@endphp
    --primary:   {{ $ws->get('primary_color','#0f766e') }};
    --primary-deep: {{ $primaryDeep }};
    --secondary: {{ $ws->get('secondary_color','#fbbf24') }};
}
* { font-family: 'Nunito', sans-serif; }
.bg-primary   { background-color: var(--primary); }
.text-primary { color: var(--primary); }
.border-primary { border-color: var(--primary); }
.bg-secondary { background-color: var(--secondary); }
.text-secondary { color: var(--secondary); }
.btn-primary {
    background-color: var(--primary);
    color: white;
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 15px;
    transition: all 0.2s;
    display: inline-block;
    text-decoration: none;
}
.btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
.btn-secondary {
    background-color: white;
    color: var(--primary);
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 15px;
    border: 2px solid white;
    transition: all 0.2s;
    display: inline-block;
    text-decoration: none;
}
.btn-secondary:hover { background: transparent; color: white; }
.section-title { font-size: 2rem; font-weight: 800; color: #1f2937; line-height: 1.2; }
.section-sub { color: #6b7280; font-size: 1.05rem; margin-top: 8px; max-width: 600px; }
nav a { transition: color 0.15s; }
[x-cloak] { display: none !important; }
</style>
</head>
<body class="bg-white" x-data="{ mobileOpen: false }">

{{-- ── NAVBAR ── --}}
<div class="fixed top-0 left-0 right-0 z-50">
    {{-- Top utility bar --}}
    <div class="text-white text-xs" style="background:var(--primary)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between py-2">
            <div class="hidden sm:flex items-center gap-4">
                @if($ws->get('contact_phone'))<span class="flex items-center gap-1">📞 {{ $ws->get('contact_phone') }}</span>@endif
                @if($ws->get('contact_email'))<span class="flex items-center gap-1">✉️ {{ $ws->get('contact_email') }}</span>@endif
            </div>
            <div class="flex items-center gap-3 ml-auto">
                <a href="{{ route('student.login') }}" class="border border-white/50 font-semibold px-3 py-1 rounded-lg hover:bg-white/10">Student Portal</a>
                @if($ws->get('admissions_open','1') === '1')
                <a href="{{ route('website.admissions') }}" class="bg-white font-bold px-3 py-1 rounded-lg hover:opacity-90" style="color:var(--primary)">Apply Now</a>
                @endif
            </div>
        </div>
    </div>
    {{-- Main nav bar --}}
    <nav class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                @if($school->logo && file_exists(public_path('storage/'.$school->logo)))
                    <img src="/storage/{{ $school->logo }}" class="h-10 w-10 rounded-full object-cover">
                @endif
                <span class="font-extrabold text-lg text-primary">{{ $school->school_name }}</span>
            </a>
            {{-- Desktop Nav --}}
            <div class="hidden md:flex items-center gap-6 text-sm font-semibold text-gray-600">
                <a href="{{ route('home') }}" class="hover:text-primary">Home</a>
                <a href="#about"        class="hover:text-primary">About</a>
                <a href="#programs"     class="hover:text-primary">Programs</a>
                <a href="#teachers"     class="hover:text-primary">Teachers</a>
                <a href="#gallery"      class="hover:text-primary">Gallery</a>
                <a href="#news"         class="hover:text-primary">News</a>
                <a href="{{ route('website.faq') }}" class="hover:text-primary">FAQ</a>
                <a href="#contact"      class="hover:text-primary">Contact</a>
            </div>
            {{-- Mobile toggle --}}
            <button @click="mobileOpen=!mobileOpen" class="md:hidden text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
        {{-- Mobile menu --}}
        <div x-show="mobileOpen" x-cloak class="md:hidden pb-4 space-y-2 text-sm font-semibold">
            <a href="#about"    class="block py-2 text-gray-600 hover:text-primary">About</a>
            <a href="#programs" class="block py-2 text-gray-600 hover:text-primary">Programs</a>
            <a href="#teachers" class="block py-2 text-gray-600 hover:text-primary">Teachers</a>
            <a href="#gallery"  class="block py-2 text-gray-600 hover:text-primary">Gallery</a>
            <a href="#news"     class="block py-2 text-gray-600 hover:text-primary">News</a>
            <a href="{{ route('website.faq') }}" class="block py-2 text-gray-600 hover:text-primary">FAQ</a>
            <a href="#contact"  class="block py-2 text-gray-600 hover:text-primary">Contact</a>
            <a href="{{ route('student.login') }}" class="block py-2 text-primary font-bold">Student Portal</a>
            @if($ws->get('admissions_open','1') === '1')
            <a href="{{ route('website.admissions') }}" class="block btn-primary text-center mt-2">Apply Now</a>
            @endif
        </div>
    </div>
    </nav>
</div>

{{-- ── HERO ── --}}
<section class="relative overflow-hidden pt-28"
         x-data="heroMedia()" x-init="init()"
         style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-deep) 100%)">

    <div class="flex flex-col-reverse lg:flex-row min-h-[60vh] lg:min-h-[75vh]">

        {{-- LEFT: Text --}}
        <div class="flex flex-col justify-center px-6 sm:px-10 lg:px-16 py-14 lg:py-24 lg:w-1/2 relative z-10">
            <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full opacity-10 bg-yellow-400 -translate-x-1/2 translate-y-1/2 pointer-events-none"></div>
            @if($school->motto)
            @php
                $mottoParts = array_map('trim', explode('•', $school->motto));
                $mottoMain  = $mottoParts[0] ?? '';
                $mottoRest  = array_slice($mottoParts, 1);
            @endphp
            <div class="inline-flex items-center gap-2 bg-white/10 text-white text-xs font-bold tracking-wide uppercase px-4 py-2 rounded-full mb-4 w-fit">
                🦅 Our Motto: {{ $mottoMain }}
            </div>
            @else
            <div class="inline-flex items-center gap-2 bg-white/10 text-white text-xs font-semibold px-4 py-2 rounded-full mb-4 w-fit">
                🏫 Excellence in Education
            </div>
            @endif
            <h1 class="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-black text-white leading-tight mb-5">
                {!! nl2br(e($ws->get('hero_headline'))) !!}
            </h1>
            <p class="text-white/80 text-base md:text-lg mb-8 leading-relaxed max-w-lg">
                {{ $ws->get('hero_subtext') }}
            </p>
            <div class="flex flex-wrap gap-4">
                @if($ws->get('admissions_open','1') === '1')
                <a href="{{ $ws->get('hero_cta_primary_link', route('website.admissions')) }}" class="btn-secondary">
                    {{ $ws->get('hero_cta_primary','Apply for Admission') }}
                </a>
                @endif
                <a href="{{ $ws->get('hero_cta_secondary_link', route('student.login')) }}"
                   class="btn-primary" style="background:var(--secondary);color:#1f2937">
                    {{ $ws->get('hero_cta_secondary','Student Portal') }}
                </a>
            </div>
        </div>

        {{-- RIGHT: Media --}}
        <div class="relative lg:w-1/2 h-64 sm:h-80 lg:h-auto overflow-hidden">
            @if($heroMedia->count())
                @foreach($heroMedia as $i => $media)
                @if($media->type === 'video')
                <video x-show="current === {{ $i }}" x-cloak
                       class="absolute inset-0 w-full h-full object-cover"
                       autoplay muted loop playsinline
                       id="hero-vid-{{ $i }}"
                       src="/storage/{{ $media->file_path }}"></video>
                @else
                <div x-show="current === {{ $i }}" x-cloak
                     class="absolute inset-0 bg-cover bg-center transition-opacity duration-1000"
                     style="background-image:url('/storage/{{ $media->file_path }}')"></div>
                @endif
                @endforeach
                {{-- Sound toggle --}}
                @if($heroMedia->where('type','video')->count())
                <button id="hero-sound-btn"
                        onclick="
                            var vids = document.querySelectorAll('[id^=hero-vid-]');
                            var m = vids[0] ? vids[0].muted : true;
                            vids.forEach(function(v){ v.muted = !m; });
                            document.getElementById('hero-mute-icon').style.display = m ? 'none' : 'inline';
                            document.getElementById('hero-unmute-icon').style.display = m ? 'inline' : 'none';
                        "
                        class="absolute bottom-4 right-4 z-20 w-9 h-9 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center">
                    <span id="hero-mute-icon" class="text-base">🔇</span>
                    <span id="hero-unmute-icon" class="text-base" style="display:none">🔊</span>
                </button>
                @endif
                {{-- Motto values floating card --}}
                @if($school->motto && !empty($mottoRest))
                <div class="hidden lg:block absolute top-3 left-3 z-10 bg-white/95 backdrop-blur rounded-xl shadow-lg p-4 max-w-[220px]">
                    <p class="text-xs font-black uppercase tracking-widest mb-2" style="color:var(--primary)">
                        🦅 {{ $mottoMain }}
                    </p>
                    <ul class="space-y-1">
                        @foreach($mottoRest as $val)
                        @if($val)
                        <li class="text-xs text-gray-600 flex items-start gap-1.5">
                            <span style="color:var(--secondary)">●</span> {{ $val }}
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Media counter dots --}}
                @if($heroMedia->count() > 1)
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                    @foreach($heroMedia as $i => $media)
                    <button @click="current={{ $i }}"
                            :class="current==={{ $i }} ? 'bg-white w-6' : 'bg-white/40 w-2'"
                            class="h-2 rounded-full transition-all duration-300"></button>
                    @endforeach
                </div>
                @endif
            @else
                {{-- Fallback if no media --}}
                <div class="absolute inset-0 flex items-center justify-center text-8xl opacity-20">🏫</div>
            @endif
        </div>

    </div>
</section>

{{-- ── STATS BAR ── --}}
<section class="py-10 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
        @foreach([
            [$ws->get('about_stat1_number','15+'), $ws->get('about_stat1_label','Years of Excellence'), '🎓'],
            [$ws->get('about_stat2_number','500+'), $ws->get('about_stat2_label','Happy Students'), '👨‍🎓'],
            [$ws->get('about_stat3_number','50+'), $ws->get('about_stat3_label','Qualified Teachers'), '👩‍🏫'],
            [$ws->get('about_stat4_number','20+'), $ws->get('about_stat4_label','Awards Won'), '🏆'],
        ] as [$num, $label, $icon])
        <div class="text-center">
            <div class="text-2xl sm:text-3xl mb-1">{{ $icon }}</div>
            <div class="text-2xl sm:text-3xl font-black" style="color:var(--primary)">{{ $num }}</div>
            <div class="text-gray-500 text-xs sm:text-sm mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>
</section>

{{-- ── ABOUT ── --}}
<section id="about" class="py-20 bg-white" x-data="{ aboutOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            {{-- Image --}}
            <div class="relative mb-10 lg:mb-0">
                @if($ws->get('about_image'))
                <img src="/storage/{{ $ws->get('about_image') }}"
                     class="w-full rounded-3xl shadow-2xl object-cover h-80 lg:h-96">
                @else
                <div class="w-full rounded-3xl h-80 lg:h-96 flex items-center justify-center text-8xl"
                     style="background: linear-gradient(135deg, #fef3c7, #fde68a)">🏫</div>
                @endif
            </div>

            {{-- Summary text --}}
            <div class="mt-8 lg:mt-0">
                <div class="text-sm font-bold text-primary uppercase tracking-widest mb-3">About Us</div>
                <h2 class="section-title mb-4">{{ $ws->get('about_headline') }}</h2>
                <p class="text-gray-500 mb-4 text-base leading-relaxed">{{ $ws->get('about_subtext') }}</p>
                <p class="text-gray-600 leading-relaxed mb-6">
                    {{ Str::limit($ws->get('about_body'), 220) }}
                </p>
                {{-- Mission/Vision pills --}}
                <div class="flex flex-wrap gap-3 mb-8">
                    @if($ws->get('about_mission'))
                    <div class="flex items-center gap-2 bg-red-50 text-primary text-xs font-semibold px-4 py-2 rounded-full">
                        🎯 Mission-driven
                    </div>
                    @endif
                    @if($ws->get('about_vision'))
                    <div class="flex items-center gap-2 bg-yellow-50 text-yellow-700 text-xs font-semibold px-4 py-2 rounded-full">
                        🔭 Vision-led
                    </div>
                    @endif
                    <div class="flex items-center gap-2 bg-green-50 text-green-700 text-xs font-semibold px-4 py-2 rounded-full">
                        ✅ Excellence-focused
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button @click="aboutOpen = true"
                            class="btn-primary">
                        Read More →
                    </button>
                    <a href="{{ route('website.admissions') }}"
                       class="btn-secondary" style="color:var(--primary);border-color:var(--primary);background:transparent">
                        Enrol Your Child
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ABOUT MODAL ── --}}
    <div x-show="aboutOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
         x-cloak
         @click.self="aboutOpen = false">

        <div x-show="aboutOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">

            {{-- Modal header --}}
            <div class="relative h-48 rounded-t-3xl overflow-hidden"
                 style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-deep) 100%)">
                @if($ws->get('about_image'))
                <img src="/storage/{{ $ws->get('about_image') }}"
                     class="absolute inset-0 w-full h-full object-cover opacity-30">
                @endif
                <div class="absolute inset-0 flex flex-col justify-end p-8">
                    <div class="text-xs font-bold text-white/60 uppercase tracking-widest mb-1">About Us</div>
                    <h2 class="text-2xl font-black text-white">{{ $ws->get('about_headline') }}</h2>
                </div>
                <button @click="aboutOpen = false"
                        class="absolute top-4 right-4 w-9 h-9 bg-white/20 hover:bg-white/40 rounded-full flex items-center justify-center text-white transition-all">
                    ✕
                </button>
            </div>

            {{-- Modal body --}}
            <div class="p-8 space-y-6">
                {{-- Subtext --}}
                @if($ws->get('about_subtext'))
                <p class="text-gray-500 text-lg leading-relaxed border-l-4 pl-4" style="border-color:var(--primary)">
                    {{ $ws->get('about_subtext') }}
                </p>
                @endif

                {{-- Full body --}}
                @if($ws->get('about_body'))
                <p class="text-gray-600 leading-relaxed">{{ $ws->get('about_body') }}</p>
                @endif

                {{-- Stats --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4">
                    @foreach([
                        [$ws->get('about_stat1_number','15+'), $ws->get('about_stat1_label','Years of Excellence'), '🎓'],
                        [$ws->get('about_stat2_number','500+'), $ws->get('about_stat2_label','Happy Students'), '👨‍🎓'],
                        [$ws->get('about_stat3_number','50+'), $ws->get('about_stat3_label','Qualified Teachers'), '👩‍🏫'],
                        [$ws->get('about_stat4_number','20+'), $ws->get('about_stat4_label','Awards Won'), '🏆'],
                    ] as [$num, $label, $icon])
                    <div class="text-center p-4 rounded-2xl" style="background:#faf9f7">
                        <div class="text-2xl mb-1">{{ $icon }}</div>
                        <div class="text-2xl font-black text-primary">{{ $num }}</div>
                        <div class="text-gray-500 text-xs mt-1">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Mission / Vision / Values --}}
                @if($ws->get('about_mission') || $ws->get('about_vision') || $ws->get('about_values'))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($ws->get('about_mission'))
                    <div class="bg-red-50 rounded-2xl p-5">
                        <div class="text-2xl mb-2">🎯</div>
                        <h4 class="font-bold text-gray-800 mb-2">Our Mission</h4>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $ws->get('about_mission') }}</p>
                    </div>
                    @endif
                    @if($ws->get('about_vision'))
                    <div class="bg-yellow-50 rounded-2xl p-5">
                        <div class="text-2xl mb-2">🔭</div>
                        <h4 class="font-bold text-gray-800 mb-2">Our Vision</h4>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $ws->get('about_vision') }}</p>
                    </div>
                    @endif
                    @if($ws->get('about_values'))
                    <div class="bg-green-50 rounded-2xl p-5">
                        <div class="text-2xl mb-2">✅</div>
                        <h4 class="font-bold text-gray-800 mb-2">Our Values</h4>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $ws->get('about_values') }}</p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- CTA --}}
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('website.admissions') }}" class="btn-primary">
                        Enrol Your Child Today
                    </a>
                    <button @click="aboutOpen = false"
                            class="px-6 py-3 rounded-full border border-gray-200 text-gray-500 text-sm font-semibold hover:bg-gray-50 transition-all">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── PROGRAMS ── --}}
<section id="programs" class="py-20" style="background:#faf9f7">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <div class="text-sm font-bold text-primary uppercase tracking-widest mb-3">What We Offer</div>
            <h2 class="section-title mx-auto">{{ $ws->get('programs_headline') }}</h2>
            <p class="section-sub mx-auto text-center mt-2">{{ $ws->get('programs_subtext') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($programs as $program)
            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all duration-200 border border-gray-100 hover:-translate-y-1">
                @if($program->image)
                <img src="/storage/{{ $program->image }}" class="w-full h-40 object-cover rounded-xl mb-4">
                @else
                <div class="w-full h-40 rounded-xl mb-4 flex items-center justify-center text-5xl"
                     style="background:linear-gradient(135deg,#fef3c7,#fde68a)">
                    {{ $program->icon ?? '📚' }}
                </div>
                @endif
                @if($program->badge)
                <span class="text-xs font-bold text-primary bg-red-50 px-3 py-1 rounded-full">{{ $program->badge }}</span>
                @endif
                <h3 class="font-bold text-gray-800 text-lg mt-3 mb-2">{{ $program->title }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $program->description }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── WHY CHOOSE US ── --}}
<section id="why" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <div class="text-sm font-bold text-primary uppercase tracking-widest mb-3">Our Advantages</div>
            <h2 class="section-title">{{ $ws->get('why_headline') }}</h2>
            <p class="section-sub mx-auto text-center mt-2">{{ $ws->get('why_subtext') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($features as $feature)
            <div class="flex gap-4 p-6 rounded-2xl border border-gray-100 hover:border-primary hover:shadow-md transition-all">
                <div class="text-4xl shrink-0">{{ $feature->icon ?? '⭐' }}</div>
                <div>
                    <h3 class="font-bold text-gray-800 mb-1">{{ $feature->title }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $feature->description }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── TEACHERS ── --}}
@if($teachers->count())
<section id="teachers" class="py-20" style="background:#faf9f7">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <div class="text-sm font-bold text-primary uppercase tracking-widest mb-3">Our Team</div>
            <h2 class="section-title">{{ $ws->get('teachers_headline') }}</h2>
            <p class="section-sub mx-auto text-center mt-2">{{ $ws->get('teachers_subtext') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($teachers as $teacher)
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all border border-gray-100">
                @if($teacher->image)
                <img src="/storage/{{ $teacher->image }}" class="w-full h-56 object-cover">
                @else
                <div class="w-full h-56 flex items-center justify-center text-6xl" style="background:linear-gradient(135deg,#fef3c7,#fde68a)">
                    👩‍🏫
                </div>
                @endif
                <div class="p-5 text-center">
                    <h3 class="font-bold text-gray-800 text-base">{{ $teacher->title }}</h3>
                    <p class="text-primary text-sm font-semibold mt-1">{{ $teacher->subtitle }}</p>
                    @if($teacher->description)
                    <p class="text-gray-500 text-xs mt-2">{{ $teacher->description }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── GALLERY ── --}}
@if($gallery->count())
<section id="gallery" class="py-20 bg-white" x-data="{ activeCategory: '{{ $galleryCategories->first() ?? 'all' }}', showAll: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <div class="text-sm font-bold text-primary uppercase tracking-widest mb-3">School Life</div>
            <h2 class="section-title">{{ $ws->get('gallery_headline','Our Gallery') }}</h2>
            <p class="section-sub mx-auto text-center mt-2">{{ $ws->get('gallery_subtext') }}</p>
        </div>
        {{-- Category filter tabs --}}
        @if($galleryCategories->count())
        <div class="flex flex-wrap justify-center gap-2 mb-10">
            @foreach($galleryCategories as $cat)
            <button @click="activeCategory='{{ $cat }}'"
                    :class="activeCategory==='{{ $cat }}' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-5 py-2 rounded-full text-sm font-semibold transition-all">{{ $cat }}</button>
            @endforeach
            <button @click="activeCategory='all'"
                    :class="activeCategory==='all' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-5 py-2 rounded-full text-sm font-semibold transition-all">All</button>
        </div>
        @endif
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @php $allCount = 0; @endphp
            @foreach($gallery as $photo)
            @if($photo->image)
            @php $allCount++ @endphp
            <div class="relative overflow-hidden rounded-2xl group aspect-square"
                 x-show="activeCategory==='{{ $photo->category }}' || (activeCategory==='all' && (showAll || {{ $allCount }} <= 8))"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <img src="/storage/{{ $photo->image }}"
                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                     loading="lazy">
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-all flex items-end p-4">
                    <div>
                        @if($photo->title)<p class="text-white font-semibold text-sm">{{ $photo->title }}</p>@endif
                        @if($photo->category)<span class="text-white/70 text-xs">{{ $photo->category }}</span>@endif
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        {{-- Show More button (only visible on All tab when there are more than 8) --}}
        @if($gallery->count() > 8)
        <div class="text-center mt-8" x-show="activeCategory==='all' && !showAll">
            <button @click="showAll = true"
                    class="inline-flex items-center gap-2 px-8 py-3 rounded-full font-bold text-sm border-2 border-primary text-primary hover:bg-primary hover:text-white transition-all">
                View All Photos
                <span class="bg-primary text-white text-xs px-2 py-0.5 rounded-full">{{ $gallery->count() }}</span>
            </button>
        </div>
        <div class="text-center mt-8" x-show="activeCategory==='all' && showAll">
            <button @click="showAll = false"
                    class="inline-flex items-center gap-2 px-8 py-3 rounded-full font-bold text-sm border-2 border-gray-300 text-gray-500 hover:bg-gray-100 transition-all">
                Show Less ↑
            </button>
        </div>
        @endif
    </div>
</section>
@endif

{{-- ── TESTIMONIALS ── --}}
@if($testimonials->count())
<section id="testimonials" class="py-20" style="background:linear-gradient(135deg,var(--primary) 0%,var(--primary-deep) 100%)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <div class="text-sm font-bold text-yellow-300 uppercase tracking-widest mb-3">Testimonials</div>
            <h2 class="section-title text-white">{{ $ws->get('testimonials_headline') }}</h2>
            <p class="text-white/70 mx-auto text-center mt-2 max-w-xl">{{ $ws->get('testimonials_subtext') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($testimonials as $t)
            <div class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/20">
                <div class="text-yellow-400 text-xl mb-3">{{ $t->badge ?? '⭐⭐⭐⭐⭐' }}</div>
                <p class="text-white/90 italic mb-4 leading-relaxed">"{{ $t->description }}"</p>
                <div class="flex items-center gap-3">
                    @if($t->image)
                    <img src="/storage/{{ $t->image }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($t->title,0,1)) }}
                    </div>
                    @endif
                    <div>
                        <p class="text-white font-bold text-sm">{{ $t->title }}</p>
                        <p class="text-white/60 text-xs">{{ $t->subtitle }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── NEWS & NOTICES ── --}}
@if($news->count() || $notices->count())
<section id="news" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <div class="text-sm font-bold text-primary uppercase tracking-widest mb-3">Latest Updates</div>
            <h2 class="section-title">{{ $ws->get('news_headline','News & Updates') }}</h2>
            <p class="section-sub mx-auto text-center mt-2">{{ $ws->get('news_subtext') }}</p>
        </div>

        {{-- News articles --}}
        @if($news->count())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            @foreach($news as $article)
            <a href="{{ route('website.news.show', $article->slug) }}"
               class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all border border-gray-100">
                @if($article->image)
                <div class="aspect-video overflow-hidden">
                    <img src="/storage/{{ $article->image }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">
                </div>
                @else
                <div class="aspect-video bg-gradient-to-br from-primary to-red-900 flex items-center justify-center">
                    <span class="text-4xl">📰</span>
                </div>
                @endif
                <div class="p-5">
                    @if($article->category)
                    <span class="text-xs font-bold text-primary bg-red-50 px-3 py-1 rounded-full">{{ $article->category }}</span>
                    @endif
                    <h3 class="font-bold text-gray-800 mt-3 mb-2 group-hover:text-primary transition-colors leading-snug">
                        {{ $article->title }}
                    </h3>
                    @if($article->excerpt)
                    <p class="text-gray-500 text-sm leading-relaxed">{{ Str::limit($article->excerpt, 100) }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-3 flex items-center gap-1">
                        📅 {{ $article->published_at?->format('d M Y') }}
                    </p>
                </div>
            </a>
            @endforeach
        </div>
        @endif

        {{-- Notices --}}
        @if($notices->count())
        <div class="border-t border-gray-200 pt-10">
            <h3 class="font-bold text-gray-700 text-lg mb-6">📢 School Notices</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($notices as $notice)
                <div class="bg-white border border-gray-100 rounded-2xl p-5 hover:shadow-md transition-all">
                    <span class="text-xs font-bold text-primary bg-red-50 px-3 py-1 rounded-full">
                        {{ ucfirst($notice->audience) }}
                    </span>
                    <h3 class="font-bold text-gray-800 mt-3 mb-2 text-sm">{{ $notice->title }}</h3>
                    <p class="text-gray-500 text-xs leading-relaxed">{{ Str::limit($notice->body, 120) }}</p>
                    <p class="text-xs text-gray-400 mt-3">{{ $notice->created_at->format('d M Y') }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endif

{{-- ── CONTACT ── --}}
<section id="contact" class="py-20" style="background:#faf9f7">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <div class="text-sm font-bold text-primary uppercase tracking-widest mb-3">Contact Us</div>
            <h2 class="section-title">{{ $ws->get('contact_headline') }}</h2>
            <p class="section-sub mx-auto text-center mt-2">{{ $ws->get('contact_subtext') }}</p>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div class="space-y-6">
                @foreach([
                    ['📍', 'Address', $ws->get('contact_address')],
                    ['📞', 'Phone', $ws->get('contact_phone')],
                    ['✉️', 'Email', $ws->get('contact_email')],
                ] as [$icon, $label, $value])
                @if($value)
                <div class="flex gap-4 items-start">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl shrink-0"
                         style="background:linear-gradient(135deg,#fef3c7,#fde68a)">{{ $icon }}</div>
                    <div>
                        <p class="font-bold text-gray-800">{{ $label }}</p>
                        <p class="text-gray-500 text-sm mt-0.5">{{ $value }}</p>
                    </div>
                </div>
                @endif
                @endforeach

                {{-- Social links --}}
                <div class="flex gap-3 pt-2">
                    @foreach([
                        ['facebook', $ws->get('footer_facebook'), 'f'],
                        ['instagram', $ws->get('footer_instagram'), '📸'],
                        ['twitter', $ws->get('footer_twitter'), '𝕏'],
                        ['whatsapp', $ws->get('footer_whatsapp'), '💬'],
                    ] as [$name, $url, $icon])
                    @if($url)
                    <a href="{{ $url }}" target="_blank"
                       class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold"
                       style="background:var(--primary)">{{ $icon }}</a>
                    @endif
                    @endforeach
                </div>
            </div>

            {{-- Quick contact form --}}
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-800 text-lg mb-6">Send us a message</h3>
                <form class="space-y-4" action="#" method="POST">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Your Name</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary" placeholder="Full name">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Phone</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary" placeholder="Phone number">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Message</label>
                        <textarea rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary" placeholder="How can we help you?"></textarea>
                    </div>
                    <button type="submit" class="btn-primary w-full text-center">Send Message</button>
                </form>
            </div>
        </div>

        {{-- Map --}}
        @if($ws->get('contact_address'))
        <div class="mt-12 rounded-2xl overflow-hidden shadow-sm border border-gray-100 h-80">
            <iframe
                width="100%" height="100%" style="border:0"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q={{ urlencode($ws->get('contact_address')) }}&output=embed">
            </iframe>
        </div>
        @endif
    </div>
</section>

{{-- ── FOOTER ── --}}
<footer class="bg-primary text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div class="md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    @if($school->logo && file_exists(public_path('storage/'.$school->logo)))
                    <img src="/storage/{{ $school->logo }}" class="h-10 w-10 rounded-full object-cover">
                    @endif
                    <span class="font-black text-xl">{{ $school->school_name }}</span>
                </div>
                <p class="text-white/70 text-sm leading-relaxed max-w-xs">{{ $ws->get('footer_tagline') }}</p>
            </div>
            <div>
                <h4 class="font-bold mb-4 text-white/90">Quick Links</h4>
                <ul class="space-y-2 text-sm text-white/60">
                    <li><a href="#about"    class="hover:text-white">About Us</a></li>
                    <li><a href="#programs" class="hover:text-white">Programs</a></li>
                    <li><a href="#teachers" class="hover:text-white">Teachers</a></li>
                    <li><a href="{{ route('website.faq') }}" class="hover:text-white">FAQ</a></li>
                    <li><a href="#contact"  class="hover:text-white">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4 text-white/90">Portals</h4>
                <ul class="space-y-2 text-sm text-white/60">
                    <li><a href="{{ route('student.login') }}" class="hover:text-white">Student Portal</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white">Staff Portal</a></li>
                    @if($ws->get('admissions_open','1') === '1')
                    <li><a href="{{ route('website.admissions') }}" class="hover:text-white">Apply Now</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="border-t border-white/20 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-white/50 text-sm">© {{ date('Y') }} {{ $school->school_name }}. All rights reserved.</p>
            <p class="text-white/30 text-xs">Powered by <a href="https://drik.tech" target="_blank" class="hover:text-white/60 transition-colors">DRiK Technologies</a></p>
        </div>
    </div>
</footer>

<script>
// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(a.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

// Hero media rotator
function heroMedia() {
    return {
        current: 0,
        total: {{ $heroMedia->count() }},
        init() {
            if (this.total <= 1) return;
            // Pick random start
            this.current = Math.floor(Math.random() * this.total);
            // Rotate every 6 seconds
            setInterval(() => {
                this.current = (this.current + 1) % this.total;
            }, 6000);
        }
    }
}
</script>
@if(config('services.anthropic.api_key'))
@include('website.partials.chatbot')
@endif
</body>
</html>
