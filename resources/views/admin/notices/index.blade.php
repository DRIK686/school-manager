@extends('layouts.admin')
@section('title','Notices')
@section('page-title','School Notices')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Post form --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-semibold text-gray-800 mb-4">Post a Notice</h2>
        <form method="POST" action="{{ route('admin.notices.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Message *</label>
                <textarea name="body" rows="5" required
                          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">{{ old('body') }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Audience</label>
                <select name="audience" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                    <option value="all">Everyone</option>
                    <option value="students">Students only</option>
                    <option value="teachers">Teachers only</option>
                    <option value="parents">Parents only</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Expires (optional)</label>
                <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
            </div>
            <button type="submit" class="w-full py-2 text-white rounded-lg text-sm font-medium" style="background:var(--sidebar-bg)">
                Publish Notice
            </button>
        </form>
    </div>

    {{-- Notices list --}}
    <div class="lg:col-span-2 space-y-3">
        @forelse($notices as $notice)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-semibold text-gray-800">{{ $notice->title }}</h3>
                        <span class="text-xs px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full">{{ ucfirst($notice->audience) }}</span>
                        @if(!$notice->is_published)
                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">Unpublished</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $notice->poster->name }} · {{ $notice->created_at->format('d M Y') }}
                        @if($notice->expires_at) · Expires {{ $notice->expires_at->format('d M Y') }} @endif
                    </p>
                    <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $notice->body }}</p>
                </div>
                <form method="POST" action="{{ route('admin.notices.destroy',$notice) }}" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs px-3 py-1 border border-red-200 rounded-lg text-red-500 shrink-0">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-16 text-center text-gray-400 text-sm">
            No notices yet. Post one on the left.
        </div>
        @endforelse
    </div>
</div>
@endsection
