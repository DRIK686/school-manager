@extends('layouts.student')
@section('title','Notices')
@section('page-title','School Notices')
@section('content')
<div class="space-y-3">
    @forelse($notices as $notice)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800">{{ $notice->title }}</h3>
        <p class="text-xs text-gray-400 mt-0.5">
            Posted by {{ $notice->poster->name }} · {{ $notice->created_at->format('d M Y') }}
            @if($notice->expires_at) · Expires {{ $notice->expires_at->format('d M Y') }} @endif
        </p>
        <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $notice->body }}</p>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-16 text-center text-gray-400 text-sm">
        No notices at this time.
    </div>
    @endforelse
</div>
@endsection
