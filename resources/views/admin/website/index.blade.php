@extends('layouts.admin')
@section('title','Website CMS')
@section('page-title','Website Management')
@section('content')
<div class="space-y-2 mb-6 flex items-center justify-between">
    <p class="text-sm text-gray-500">Customize your public website. Changes are live immediately.</p>
    <a href="{{ route('home') }}" target="_blank"
       class="text-xs px-4 py-2 rounded-lg text-white flex items-center gap-2" style="background:var(--sidebar-bg)">
        🌐 View Website
    </a>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
@endif

<div x-data="{ tab: 'hero' }" class="bg-white rounded-xl border border-gray-100 shadow-sm">
    {{-- Tab bar --}}
    <div class="flex overflow-x-auto border-b border-gray-100 px-4 gap-1 pt-3">
        @foreach([
            ['hero','🏠 Hero'],['about','ℹ️ About'],['programs','📚 Programs'],
            ['why','⭐ Why Us'],['teachers','👩‍🏫 Teachers'],['gallery','🖼 Gallery'],
            ['testimonials','💬 Testimonials'],['faq','❓ FAQ'],['news','📰 News'],['contact','📞 Contact'],['global','🎨 Design'],
        ] as [$key,$label])
        <button @click="tab='{{ $key }}'"
                :class="tab==='{{ $key }}' ? 'border-b-2 text-gray-900 font-bold' : 'text-gray-400 hover:text-gray-600'"
                class="px-4 py-2 text-sm whitespace-nowrap border-b-2 border-transparent -mb-px transition-all"
                style="{{ "tab==='{$key}' ? 'border-color:var(--sidebar-bg)' : ''" }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <div class="p-6">

    {{-- HERO --}}
    <div x-show="tab==='hero'" x-cloak class="space-y-6">

        {{-- Text settings --}}
        <form method="POST" action="{{ route('admin.website.save') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf <input type="hidden" name="section" value="hero">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Main Headline</label>
                    <input type="text" name="hero_headline" value="{{ \App\Models\WebsiteSetting::get('hero_headline') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Hero Badge <span class="text-gray-400">(small label above the headline; leave empty to use the school motto)</span></label>
                    <input type="text" name="hero_badge" value="{{ \App\Models\WebsiteSetting::get('hero_badge','') }}" placeholder="e.g. 🏫 Excellence in Education"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Motto Icon <span class="text-gray-400">(optional emoji shown beside the school motto)</span></label>
                    <input type="text" name="motto_icon" value="{{ \App\Models\WebsiteSetting::get('motto_icon','') }}" placeholder="e.g. 🌟" maxlength="8"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtext</label>
                    <textarea name="hero_subtext" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">{{ \App\Models\WebsiteSetting::get('hero_subtext') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Primary Button Text</label>
                    <input type="text" name="hero_cta_primary" value="{{ \App\Models\WebsiteSetting::get('hero_cta_primary') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Primary Button Link</label>
                    <input type="text" name="hero_cta_primary_link" value="{{ \App\Models\WebsiteSetting::get('hero_cta_primary_link') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Secondary Button Text</label>
                    <input type="text" name="hero_cta_secondary" value="{{ \App\Models\WebsiteSetting::get('hero_cta_secondary') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Secondary Button Link</label>
                    <input type="text" name="hero_cta_secondary_link" value="{{ \App\Models\WebsiteSetting::get('hero_cta_secondary_link') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Admissions Open?</label>
                    <select name="admissions_open" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                        <option value="1" {{ \App\Models\WebsiteSetting::get('admissions_open') === '1' ? 'selected' : '' }}>Yes — Show Apply Button</option>
                        <option value="0" {{ \App\Models\WebsiteSetting::get('admissions_open') === '0' ? 'selected' : '' }}>No — Hide Apply Button</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary text-sm px-6 py-2">Save Hero Settings</button>
        </form>

        {{-- Hero Media (images/videos) --}}
        <div class="border-t border-gray-100 pt-6">
            <p class="text-sm font-bold text-gray-700 mb-1">Hero Background Media</p>
            <p class="text-xs text-gray-400 mb-4">Upload images or videos. One is picked at random on each page load. Supports JPG, PNG, WebP, MP4. Max 100 MB per file.</p>

            {{-- Existing media --}}
            @if($heroMedia->count())
            <div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-4">
                @foreach($heroMedia as $media)
                <div class="relative group rounded-lg overflow-hidden aspect-video bg-gray-100">
                    @if($media->type === 'video')
                    <video src="/storage/{{ $media->file_path }}" class="w-full h-full object-cover" muted></video>
                    <span class="absolute bottom-1 left-1 text-white text-xs bg-black/50 px-1 rounded">▶ video</span>
                    @else
                    <img src="/storage/{{ $media->file_path }}" class="w-full h-full object-cover">
                    @endif
                    <form method="POST" action="{{ route('admin.website.hero-media.destroy', $media) }}"
                          class="absolute top-1 right-1 opacity-0 group-hover:opacity-100">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Remove this media?')"
                                class="w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center leading-none">✕</button>
                    </form>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-400 italic mb-4">No hero media yet. Upload below.</p>
            @endif

            {{-- Upload form --}}
            <form method="POST" action="{{ route('admin.website.hero-media.store') }}" enctype="multipart/form-data"
                  class="border border-dashed border-gray-200 rounded-xl p-4 space-y-3">
                @csrf
                <label class="block text-xs text-gray-500 font-semibold uppercase">Upload Images / Videos</label>
                <input type="file" name="files[]" accept="image/*,video/mp4,video/mov" multiple required
                       class="text-sm text-gray-600 w-full">
                <p class="text-xs text-gray-400">Hold Ctrl/Cmd to select multiple files at once.</p>
                <button type="submit" class="text-xs px-4 py-2 text-white rounded-lg" style="background:var(--sidebar-bg)">
                    Upload Media
                </button>
            </form>
        </div>
    </div>

    {{-- ABOUT --}}
    <div x-show="tab==='about'" x-cloak>
        <form method="POST" action="{{ route('admin.website.save') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf <input type="hidden" name="section" value="about">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Headline</label>
                    <input type="text" name="about_headline" value="{{ \App\Models\WebsiteSetting::get('about_headline') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtext</label>
                    <input type="text" name="about_subtext" value="{{ \App\Models\WebsiteSetting::get('about_subtext') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Body Text</label>
                    <textarea name="about_body" rows="4" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">{{ \App\Models\WebsiteSetting::get('about_body') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">About Image</label>
                    @if(\App\Models\WebsiteSetting::get('about_image'))
                    <img src="/storage/{{ \App\Models\WebsiteSetting::get('about_image') }}" class="h-20 rounded-lg mb-2 object-cover">
                    @endif
                    <input type="file" name="about_image" accept="image/*" class="text-sm text-gray-600">
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mt-2">Stats</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([1,2,3,4] as $i)
                <div class="border border-gray-100 rounded-lg p-3">
                    <label class="block text-xs text-gray-500 mb-1">Stat {{ $i }} Number</label>
                    <input type="text" name="about_stat{{ $i }}_number" value="{{ \App\Models\WebsiteSetting::get('about_stat'.$i.'_number') }}"
                           class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none mb-2" placeholder="e.g. 500+">
                    <label class="block text-xs text-gray-500 mb-1">Stat {{ $i }} Label</label>
                    <input type="text" name="about_stat{{ $i }}_label" value="{{ \App\Models\WebsiteSetting::get('about_stat'.$i.'_label') }}"
                           class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none" placeholder="e.g. Happy Students">
                </div>
                @endforeach
            </div>
            <div class="border-t border-gray-100 pt-4 mt-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Mission, Vision & Values <span class="text-gray-400 font-normal">(shown in the Read More modal)</span></p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">🎯 Our Mission</label>
                        <textarea name="about_mission" rows="4" placeholder="What is your school's mission?"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">{{ \App\Models\WebsiteSetting::get('about_mission') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">🔭 Our Vision</label>
                        <textarea name="about_vision" rows="4" placeholder="What is your school's vision?"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">{{ \App\Models\WebsiteSetting::get('about_vision') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">✅ Our Values</label>
                        <textarea name="about_values" rows="4" placeholder="What are your school's core values?"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">{{ \App\Models\WebsiteSetting::get('about_values') }}</textarea>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-primary text-sm px-6 py-2">Save About Section</button>
        </form>
    </div>

    {{-- PROGRAMS --}}
    <div x-show="tab==='programs'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('admin.website.save') }}" class="space-y-3">
            @csrf <input type="hidden" name="section" value="programs">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section Headline</label>
                    <input type="text" name="programs_headline" value="{{ \App\Models\WebsiteSetting::get('programs_headline') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtext</label>
                    <input type="text" name="programs_subtext" value="{{ \App\Models\WebsiteSetting::get('programs_subtext') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
            </div>
            <button type="submit" class="btn-primary text-sm px-4 py-2">Save Headlines</button>
        </form>

        {{-- Existing programs --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($programs as $prog)
            <div class="border border-gray-100 rounded-xl p-4">
                <form method="POST" action="{{ route('admin.website.items.update', $prog) }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <input type="hidden" name="type" value="program">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Title</label>
                            <input type="text" name="title" value="{{ $prog->title }}" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Badge (e.g. Ages 3-5)</label>
                            <input type="text" name="badge" value="{{ $prog->badge }}" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Icon (emoji)</label>
                            <input type="text" name="icon" value="{{ $prog->icon }}" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Image</label>
                            <input type="file" name="image" accept="image/*" class="text-xs text-gray-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">{{ $prog->description }}</textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="text-xs px-3 py-1 text-white rounded" style="background:var(--sidebar-bg)">Update</button>
                        <button type="button" onclick="if(confirm('Delete?')) { this.closest('form').action='{{ route('admin.website.items.destroy', $prog) }}'; this.closest('form').submit(); }"
                                class="text-xs px-3 py-1 border border-red-200 text-red-500 rounded">Delete</button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>

        {{-- Add new program --}}
        <div class="border border-dashed border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Add New Program</p>
            <form method="POST" action="{{ route('admin.website.items.store') }}" enctype="multipart/form-data" class="space-y-2">
                @csrf <input type="hidden" name="type" value="program">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <input type="text" name="title" required placeholder="Program Title" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    <input type="text" name="badge" placeholder="Ages 5-8" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    <input type="text" name="icon" placeholder="📚" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    <input type="file" name="image" accept="image/*" class="text-xs text-gray-500">
                </div>
                <textarea name="description" rows="2" placeholder="Description..." class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none"></textarea>
                <button type="submit" class="text-xs px-4 py-1.5 text-white rounded" style="background:var(--sidebar-bg)">Add Program</button>
            </form>
        </div>
    </div>

    {{-- FAQ --}}
    <div x-show="tab==='faq'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('admin.website.save') }}" class="space-y-3">
            @csrf <input type="hidden" name="section" value="faq">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section Headline</label>
                    <input type="text" name="faq_headline" value="{{ \App\Models\WebsiteSetting::get('faq_headline') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtext</label>
                    <input type="text" name="faq_subtext" value="{{ \App\Models\WebsiteSetting::get('faq_subtext') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
            </div>
            <button type="submit" class="btn-primary text-sm px-4 py-2">Save Headlines</button>
        </form>

        {{-- Existing FAQs --}}
        <div class="space-y-3">
            @foreach($faqs as $faq)
            <div class="border border-gray-100 rounded-xl p-4">
                <form method="POST" action="{{ route('admin.website.items.update', $faq) }}" class="space-y-2">
                    @csrf
                    <input type="hidden" name="type" value="faq">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">Question</label>
                            <input type="text" name="title" value="{{ $faq->title }}" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Order</label>
                            <input type="number" name="sort_order" value="{{ $faq->sort_order }}" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Answer</label>
                        <textarea name="description" rows="3" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">{{ $faq->description }}</textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="text-xs px-3 py-1 text-white rounded" style="background:var(--sidebar-bg)">Update</button>
                        <button type="button" onclick="if(confirm('Delete this FAQ?')) { this.closest('form').action='{{ route('admin.website.items.destroy', $faq) }}'; this.closest('form').submit(); }"
                                class="text-xs px-3 py-1 border border-red-200 text-red-500 rounded">Delete</button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>

        {{-- Add new FAQ --}}
        <div class="border border-dashed border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Add New FAQ</p>
            <form method="POST" action="{{ route('admin.website.items.store') }}" class="space-y-2">
                @csrf <input type="hidden" name="type" value="faq">
                <div class="grid grid-cols-3 gap-2">
                    <input type="text" name="title" required placeholder="Question" class="col-span-2 px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    <input type="number" name="sort_order" placeholder="Order" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                </div>
                <textarea name="description" rows="3" placeholder="Answer..." class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none"></textarea>
                <button type="submit" class="text-xs px-4 py-1.5 text-white rounded" style="background:var(--sidebar-bg)">Add FAQ</button>
            </form>
        </div>
    </div>

    {{-- WHY CHOOSE US --}}
    <div x-show="tab==='why'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('admin.website.save') }}" class="space-y-3">
            @csrf <input type="hidden" name="section" value="why">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Headline</label>
                    <input type="text" name="why_headline" value="{{ \App\Models\WebsiteSetting::get('why_headline') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtext</label>
                    <input type="text" name="why_subtext" value="{{ \App\Models\WebsiteSetting::get('why_subtext') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
            </div>
            <button type="submit" class="btn-primary text-sm px-4 py-2">Save Headlines</button>
        </form>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($features as $feat)
            <div class="border border-gray-100 rounded-lg p-3">
                <form method="POST" action="{{ route('admin.website.items.update', $feat) }}" class="space-y-2">
                    @csrf
                    <div class="grid grid-cols-3 gap-2">
                        <input type="text" name="icon" value="{{ $feat->icon }}" placeholder="⭐" class="px-2 py-1.5 border border-gray-200 rounded text-xs w-16">
                        <input type="text" name="title" value="{{ $feat->title }}" placeholder="Title" class="col-span-2 px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    </div>
                    <textarea name="description" rows="2" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">{{ $feat->description }}</textarea>
                    <div class="flex gap-2">
                        <button type="submit" class="text-xs px-3 py-1 text-white rounded" style="background:var(--sidebar-bg)">Update</button>
                        <button type="button" onclick="if(confirm('Delete?')) { this.closest('form').action='{{ route('admin.website.items.destroy', $feat) }}'; this.closest('form').submit(); }"
                                class="text-xs px-3 py-1 border border-red-200 text-red-500 rounded">Delete</button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
        <div class="border border-dashed border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Add Feature</p>
            <form method="POST" action="{{ route('admin.website.items.store') }}" class="flex gap-2 flex-wrap">
                @csrf <input type="hidden" name="type" value="feature">
                <input type="text" name="icon" placeholder="⭐" class="w-12 px-2 py-1.5 border border-gray-200 rounded text-xs">
                <input type="text" name="title" required placeholder="Feature title" class="flex-1 px-2 py-1.5 border border-gray-200 rounded text-xs min-w-32">
                <input type="text" name="description" placeholder="Description" class="flex-1 px-2 py-1.5 border border-gray-200 rounded text-xs min-w-48">
                <button type="submit" class="text-xs px-4 py-1.5 text-white rounded" style="background:var(--sidebar-bg)">Add</button>
            </form>
        </div>
    </div>

    {{-- TEACHERS --}}
    <div x-show="tab==='teachers'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('admin.website.save') }}" class="space-y-3">
            @csrf <input type="hidden" name="section" value="teachers">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section Headline</label>
                    <input type="text" name="teachers_headline" value="{{ \App\Models\WebsiteSetting::get('teachers_headline') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtext</label>
                    <input type="text" name="teachers_subtext" value="{{ \App\Models\WebsiteSetting::get('teachers_subtext') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
            </div>
            <button type="submit" class="btn-primary text-sm px-4 py-2">Save Headlines</button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($teachers as $teacher)
            <div class="border border-gray-100 rounded-xl overflow-hidden">
                @if($teacher->image)
                <img src="/storage/{{ $teacher->image }}" class="w-full h-40 object-cover">
                @endif
                <div class="p-3">
                    <form method="POST" action="{{ route('admin.website.items.update', $teacher) }}" enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <input type="text" name="title" value="{{ $teacher->title }}" placeholder="Teacher Name" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                        <input type="text" name="subtitle" value="{{ $teacher->subtitle }}" placeholder="Subject / Role" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                        <input type="text" name="description" value="{{ $teacher->description }}" placeholder="Short bio" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                        <input type="file" name="image" accept="image/*" class="text-xs text-gray-500 w-full">
                        <div class="flex gap-2">
                            <button type="submit" class="text-xs px-3 py-1 text-white rounded" style="background:var(--sidebar-bg)">Update</button>
                            <button type="button" onclick="if(confirm('Delete?')) { this.closest('form').action='{{ route('admin.website.items.destroy', $teacher) }}'; this.closest('form').submit(); }"
                                    class="text-xs px-3 py-1 border border-red-200 text-red-500 rounded">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <div class="border border-dashed border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Add Teacher Card</p>
            <form method="POST" action="{{ route('admin.website.items.store') }}" enctype="multipart/form-data" class="space-y-2">
                @csrf <input type="hidden" name="type" value="teacher">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <input type="text" name="title" required placeholder="Full Name" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    <input type="text" name="subtitle" placeholder="Subject / Role" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    <input type="text" name="description" placeholder="Short bio" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    <input type="file" name="image" accept="image/*" class="text-xs text-gray-500">
                </div>
                <button type="submit" class="text-xs px-4 py-1.5 text-white rounded" style="background:var(--sidebar-bg)">Add Teacher</button>
            </form>
        </div>
    </div>

    {{-- GALLERY --}}
    <div x-show="tab==='gallery'" x-cloak class="space-y-6">
        {{-- Section headlines --}}
        <form method="POST" action="{{ route('admin.website.save') }}" class="space-y-3">
            @csrf <input type="hidden" name="section" value="gallery">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section Headline</label>
                    <input type="text" name="gallery_headline" value="{{ \App\Models\WebsiteSetting::get('gallery_headline') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtext</label>
                    <input type="text" name="gallery_subtext" value="{{ \App\Models\WebsiteSetting::get('gallery_subtext') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
            </div>
            <button type="submit" class="btn-primary text-sm px-4 py-2">Save Headlines</button>
        </form>

        {{-- Existing photos with inline category assignment --}}
        @if($gallery->count())
        <div>
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">
                    All Photos <span class="font-normal text-gray-400">({{ $gallery->count() }})</span>
                </p>
                <button type="submit" form="bulk-category-form"
                        class="text-xs px-4 py-2 text-white rounded-lg shadow"
                        style="background:var(--sidebar-bg)">
                    💾 Save All Categories
                </button>
            </div>
            <form id="bulk-category-form" method="POST" action="{{ route('admin.website.gallery.bulk-categorize') }}">
                @csrf
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($gallery as $photo)
                    @if($photo->image)
                    <div class="relative group">
                        <input type="hidden" name="photos[{{ $photo->id }}][id]" value="{{ $photo->id }}">
                        <img src="/storage/{{ $photo->image }}" class="w-full aspect-square object-cover rounded-xl">
                        {{-- Delete button — uses JS to avoid nested form --}}
                        <button type="button"
                                onclick="if(confirm('Remove photo?')) { document.getElementById('del-{{ $photo->id }}').submit(); }"
                                class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center leading-none shadow">✕</button>
                        {{-- Category + caption inputs (part of bulk form) --}}
                        <div class="mt-1 space-y-1">
                            <input type="text" name="photos[{{ $photo->id }}][category]"
                                   value="{{ $photo->category }}"
                                   placeholder="Category"
                                   list="cat-list"
                                   class="w-full px-2 py-1 border border-gray-200 rounded text-xs focus:outline-none focus:border-gray-400">
                            <input type="text" name="photos[{{ $photo->id }}][title]"
                                   value="{{ $photo->title }}"
                                   placeholder="Caption (optional)"
                                   class="w-full px-2 py-1 border border-gray-200 rounded text-xs focus:outline-none focus:border-gray-400">
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </form>

            {{-- Delete forms outside the bulk form --}}
            @foreach($gallery as $photo)
            <form id="del-{{ $photo->id }}" method="POST"
                  action="{{ route('admin.website.items.destroy', $photo) }}" style="display:none">
                @csrf @method('DELETE')
            </form>
            @endforeach
            {{-- Shared datalist for all category inputs --}}
            <datalist id="cat-list">
                @foreach($galleryCategories as $cat)
                <option value="{{ $cat }}">
                @endforeach
                <option value="Sports">
                <option value="Graduation">
                <option value="Speech & Prize Giving">
                <option value="Classroom">
                <option value="Cultural">
                <option value="Events">
            </datalist>
        </div>
        @else
        <p class="text-xs text-gray-400 italic">No gallery photos yet.</p>
        @endif

        {{-- Upload form --}}
        <div class="border border-dashed border-gray-200 rounded-xl p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Upload Photos</p>
            <form method="POST" action="{{ route('admin.website.items.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf <input type="hidden" name="type" value="gallery">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Category <span class="text-gray-400">(e.g. Sports, Graduation)</span></label>
                        <input type="text" name="category" placeholder="e.g. Sports Day"
                               list="gallery-cats"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                        <datalist id="gallery-cats">
                            @foreach($galleryCategories as $cat)
                            <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Caption (optional)</label>
                        <input type="text" name="title" placeholder="Photo caption"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Photos <span class="text-gray-400">(select multiple)</span></label>
                        <input type="file" name="images[]" accept="image/*" multiple required class="text-xs text-gray-500 w-full">
                    </div>
                </div>
                <p class="text-xs text-gray-400">Hold Ctrl/Cmd to select multiple images at once.</p>
                <button type="submit" class="text-xs px-4 py-2 text-white rounded-lg" style="background:var(--sidebar-bg)">
                    Upload Photos
                </button>
            </form>
        </div>
    </div>

    {{-- TESTIMONIALS --}}
    <div x-show="tab==='testimonials'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('admin.website.save') }}" class="space-y-3">
            @csrf <input type="hidden" name="section" value="testimonials">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Headline</label>
                    <input type="text" name="testimonials_headline" value="{{ \App\Models\WebsiteSetting::get('testimonials_headline') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtext</label>
                    <input type="text" name="testimonials_subtext" value="{{ \App\Models\WebsiteSetting::get('testimonials_subtext') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
            </div>
            <button type="submit" class="btn-primary text-sm px-4 py-2">Save Headlines</button>
        </form>
        <div class="space-y-3">
            @foreach($testimonials as $t)
            <div class="border border-gray-100 rounded-xl p-4">
                <form method="POST" action="{{ route('admin.website.items.update', $t) }}" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @csrf
                    <input type="text" name="title" value="{{ $t->title }}" placeholder="Parent Name" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    <input type="text" name="subtitle" value="{{ $t->subtitle }}" placeholder="e.g. Parent of JHS 2 student" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    <input type="text" name="badge" value="{{ $t->badge }}" placeholder="⭐⭐⭐⭐⭐" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                    <input type="file" name="image" accept="image/*" class="text-xs text-gray-500">
                    <textarea name="description" rows="2" placeholder="Quote..." class="col-span-2 md:col-span-3 px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">{{ $t->description }}</textarea>
                    <div class="flex flex-col gap-2">
                        <button type="submit" class="text-xs px-3 py-1 text-white rounded" style="background:var(--sidebar-bg)">Update</button>
                        <button type="button" onclick="if(confirm('Delete?')) { this.closest('form').action='{{ route('admin.website.items.destroy', $t) }}'; this.closest('form').submit(); }"
                                class="text-xs px-3 py-1 border border-red-200 text-red-500 rounded">Delete</button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
        <div class="border border-dashed border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Add Testimonial</p>
            <form method="POST" action="{{ route('admin.website.items.store') }}" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @csrf <input type="hidden" name="type" value="testimonial">
                <input type="text" name="title" required placeholder="Parent Name" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                <input type="text" name="subtitle" placeholder="e.g. Parent of Basic 3 student" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                <input type="text" name="badge" placeholder="⭐⭐⭐⭐⭐" class="px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                <input type="file" name="image" accept="image/*" class="text-xs text-gray-500">
                <textarea name="description" required rows="2" placeholder="Parent's quote..." class="col-span-2 md:col-span-3 px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none"></textarea>
                <button type="submit" class="text-xs px-4 py-1.5 text-white rounded self-end" style="background:var(--sidebar-bg)">Add</button>
            </form>
        </div>
    </div>


    {{-- NEWS --}}
    <div x-show="tab==='news'" x-cloak class="space-y-6">

        {{-- Section headlines --}}
        <form method="POST" action="{{ route('admin.website.save') }}" class="space-y-3">
            @csrf <input type="hidden" name="section" value="news">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section Headline</label>
                    <input type="text" name="news_headline" value="{{ \App\Models\WebsiteSetting::get('news_headline','News & Updates') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtext</label>
                    <input type="text" name="news_subtext" value="{{ \App\Models\WebsiteSetting::get('news_subtext') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
            </div>
            <button type="submit" class="btn-primary text-sm px-4 py-2">Save Headlines</button>
        </form>

        {{-- Existing articles --}}
        @if($news->count())
        <div class="space-y-3">
            @foreach($news as $article)
            <div class="border border-gray-100 rounded-xl p-4" x-data="{ editing: false }">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        @if($article->image)
                        <img src="/storage/{{ $article->image }}" class="w-16 h-12 object-cover rounded-lg shrink-0">
                        @else
                        <div class="w-16 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-2xl shrink-0">📰</div>
                        @endif
                        <div>
                            <p class="font-semibold text-sm text-gray-800">{{ $article->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $article->category ? "[$article->category] · " : '' }}
                                {{ $article->published_at?->format('d M Y') }} ·
                                <span class="{{ $article->published ? 'text-green-500' : 'text-red-400' }}">
                                    {{ $article->published ? 'Published' : 'Draft' }}
                                </span>
                            </p>
                            @if($article->excerpt)
                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($article->excerpt, 80) }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button @click="editing=!editing"
                                class="text-xs px-3 py-1 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50">
                            ✏️ Edit
                        </button>
                        <form method="POST" action="{{ route('admin.website.news.destroy', $article) }}">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete this article?')"
                                    class="text-xs px-3 py-1 border border-red-200 text-red-500 rounded-lg hover:bg-red-50">
                                🗑 Delete
                            </button>
                        </form>
                    </div>
                </div>
                {{-- Edit form --}}
                <div x-show="editing" x-cloak class="mt-4 border-t border-gray-100 pt-4">
                    <form method="POST" action="{{ route('admin.website.news.update', $article) }}"
                          enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs text-gray-500 mb-1">Title</label>
                                <input type="text" name="title" value="{{ $article->title }}" required
                                       class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Category</label>
                                <input type="text" name="category" value="{{ $article->category }}"
                                       placeholder="e.g. Events, Sports"
                                       class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs text-gray-500 mb-1">Excerpt (shown on homepage)</label>
                                <textarea name="excerpt" rows="2"
                                          class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none">{{ $article->excerpt }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Cover Image</label>
                                <input type="file" name="image" accept="image/*" class="text-xs text-gray-500 w-full">
                                <label class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                                    <input type="checkbox" name="published" value="1" {{ $article->published ? 'checked' : '' }}>
                                    Published
                                </label>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs text-gray-500 mb-1">Full Article Body</label>
                                <textarea name="body" rows="6"
                                          class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none font-mono">{{ $article->body }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="text-xs px-4 py-2 text-white rounded-lg" style="background:var(--sidebar-bg)">
                            Update Article
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-xs text-gray-400 italic">No news articles yet. Add one below.</p>
        @endif

        {{-- Add new article --}}
        <div class="border border-dashed border-gray-200 rounded-xl p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Add News Article</p>
            <form method="POST" action="{{ route('admin.website.news.store') }}"
                  enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Title *</label>
                        <input type="text" name="title" required placeholder="Article title"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Category</label>
                        <input type="text" name="category" placeholder="e.g. Events, Sports, Academic"
                               class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Excerpt <span class="text-gray-400">(shown on homepage)</span></label>
                        <textarea name="excerpt" rows="2" placeholder="Brief summary..."
                                  class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Cover Image</label>
                        <input type="file" name="image" accept="image/*" class="text-xs text-gray-500 w-full">
                        <div class="mt-2">
                            <label class="block text-xs text-gray-500 mb-1">Date</label>
                            <input type="date" name="published_at" value="{{ date('Y-m-d') }}"
                                   class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none">
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs text-gray-500 mb-1">Full Article Body <span class="text-gray-400">(optional)</span></label>
                        <textarea name="body" rows="6" placeholder="Full article content..."
                                  class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none font-mono"></textarea>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button type="submit" class="text-xs px-4 py-2 text-white rounded-lg" style="background:var(--sidebar-bg)">
                        Publish Article
                    </button>
                    <label class="flex items-center gap-2 text-xs text-gray-500">
                        <input type="checkbox" name="published" value="1" checked> Publish immediately
                    </label>
                </div>
            </form>
        </div>
    </div>

    {{-- CONTACT --}}
    <div x-show="tab==='contact'" x-cloak>
        <form method="POST" action="{{ route('admin.website.save') }}" class="space-y-4">
            @csrf <input type="hidden" name="section" value="contact">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Headline</label>
                    <input type="text" name="contact_headline" value="{{ \App\Models\WebsiteSetting::get('contact_headline') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtext</label>
                    <input type="text" name="contact_subtext" value="{{ \App\Models\WebsiteSetting::get('contact_subtext') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <input type="text" name="contact_address" value="{{ \App\Models\WebsiteSetting::get('contact_address') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="contact_phone" value="{{ \App\Models\WebsiteSetting::get('contact_phone') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" name="contact_email" value="{{ \App\Models\WebsiteSetting::get('contact_email') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Social Media</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach(['footer_facebook'=>'Facebook URL','footer_instagram'=>'Instagram URL','footer_twitter'=>'Twitter/X URL','footer_whatsapp'=>'WhatsApp Link'] as $key=>$label)
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                    <input type="url" name="{{ $key }}" value="{{ \App\Models\WebsiteSetting::get($key) }}"
                           class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:outline-none" placeholder="https://...">
                </div>
                @endforeach
            </div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Footer</p>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Footer Tagline</label>
                <input type="text" name="footer_tagline" value="{{ \App\Models\WebsiteSetting::get('footer_tagline') }}"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
            </div>
            <button type="submit" class="btn-primary text-sm px-6 py-2">Save Contact & Footer</button>
        </form>
    </div>

    {{-- DESIGN / GLOBAL --}}
    <div x-show="tab==='global'" x-cloak>
        <form method="POST" action="{{ route('admin.website.save') }}" class="space-y-4">
            @csrf <input type="hidden" name="section" value="global">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Primary Colour</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color" value="{{ \App\Models\WebsiteSetting::get('primary_color','#0f766e') }}"
                               class="h-10 w-16 rounded cursor-pointer border border-gray-200">
                        <input type="text" id="primary_color_text" value="{{ \App\Models\WebsiteSetting::get('primary_color','#0f766e') }}"
                               class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none font-mono"
                               oninput="document.querySelector('[name=primary_color]').value=this.value">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Used for hero background, buttons, and accents</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Secondary / Accent Colour</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="secondary_color" value="{{ \App\Models\WebsiteSetting::get('secondary_color','#14b8a6') }}"
                               class="h-10 w-16 rounded cursor-pointer border border-gray-200">
                        <input type="text" value="{{ \App\Models\WebsiteSetting::get('secondary_color','#14b8a6') }}"
                               class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none font-mono"
                               oninput="document.querySelector('[name=secondary_color]').value=this.value">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Used for student portal button and highlights</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Admissions Open?</label>
                    <select name="admissions_open" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                        <option value="1" {{ \App\Models\WebsiteSetting::get('admissions_open') === '1' ? 'selected' : '' }}>Yes — Show Apply Buttons</option>
                        <option value="0" {{ \App\Models\WebsiteSetting::get('admissions_open') === '0' ? 'selected' : '' }}>No — Hide Apply Buttons</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Hides all "Apply Now" buttons across the site</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Gradient End Colour</label>
                    <input type="text" name="gradient_end_color" value="{{ \App\Models\WebsiteSetting::get('gradient_end_color','') }}"
                           placeholder="Automatic" maxlength="7" pattern="#[0-9a-fA-F]{6}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none font-mono">
                    <p class="text-xs text-gray-400 mt-1">Darker end of the hero and highlight sections. Leave empty for an automatic shade of the primary colour. Format: #450a0a</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs font-semibold text-gray-600 mb-2">Live Preview</p>
                <div class="flex gap-3 items-center">
                    <div class="h-10 w-10 rounded-full" id="preview-primary"
                         style="background:{{ \App\Models\WebsiteSetting::get('primary_color','#0f766e') }}"></div>
                    <div class="h-10 w-10 rounded-full" id="preview-secondary"
                         style="background:{{ \App\Models\WebsiteSetting::get('secondary_color','#14b8a6') }}"></div>
                    <span class="text-sm font-bold" id="preview-text"
                          style="color:{{ \App\Models\WebsiteSetting::get('primary_color','#0f766e') }}">
                        {{ $school->school_name }}
                    </span>
                </div>
            </div>
            <button type="submit" class="btn-primary text-sm px-6 py-2">Save Design Settings</button>
        </form>
    </div>

    </div>{{-- end .p-6 --}}
</div>

<script>
document.querySelector('[name=primary_color]')?.addEventListener('input', function() {
    document.getElementById('primary_color_text').value = this.value;
    document.getElementById('preview-primary').style.background = this.value;
    document.getElementById('preview-text').style.color = this.value;
});
document.querySelector('[name=secondary_color]')?.addEventListener('input', function() {
    document.getElementById('preview-secondary').style.background = this.value;
});
</script>
@endsection
