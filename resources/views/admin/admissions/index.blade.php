@extends('layouts.admin')
@section('title', 'Admission Enquiries')
@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Admission Enquiries</h2>
            <p class="text-sm text-gray-500">{{ $enquiries->total() }} enquir{{ $enquiries->total() === 1 ? 'y' : 'ies' }} found</p>
        </div>
    </div>

    {{-- Status filter tabs --}}
    <div class="flex gap-2 flex-wrap">
        @php
            $tabs = [
                '' => 'All',
                'new' => 'New',
                'contacted' => 'Contacted',
                'enrolled' => 'Enrolled',
                'declined' => 'Declined',
            ];
        @endphp
        @foreach($tabs as $value => $label)
        <a href="{{ route('admin.admissions.index', $value ? ['status' => $value] : []) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium border
                  {{ request('status', '') === $value ? 'bg-primary text-white border-primary' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
            {{ $label }}
            @if($value && isset($counts[$value]))
                <span class="ml-1 opacity-75">({{ $counts[$value] }})</span>
            @endif
        </a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        @if($enquiries->isEmpty())
        <div class="px-5 py-16 text-center text-gray-400 text-sm">No admission enquiries found.</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($enquiries as $e)
            <div class="px-5 py-4" x-data="{ open: false }">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-gray-800">{{ $e->child_name }}</span>
                            @php
                                $statusColors = [
                                    'new' => 'bg-blue-50 text-blue-700',
                                    'contacted' => 'bg-amber-50 text-amber-700',
                                    'enrolled' => 'bg-green-50 text-green-700',
                                    'declined' => 'bg-gray-100 text-gray-500',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$e->status] ?? '' }}">
                                {{ ucfirst($e->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Applying for {{ $e->grade ?: '—' }} · DOB {{ \Carbon\Carbon::parse($e->dob)->format('d M Y') }} · Submitted {{ \Carbon\Carbon::parse($e->created_at)->diffForHumans() }}
                        </p>
                    </div>
                    <button @click="open=!open" class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 shrink-0">
                        Details
                    </button>
                </div>

                <div x-show="open" x-cloak class="mt-4 space-y-3 border-t border-gray-50 pt-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div><p class="text-xs text-gray-400">Parent / Guardian</p><p class="text-gray-800">{{ $e->parent_name }}</p></div>
                        <div><p class="text-xs text-gray-400">Phone</p><p class="text-gray-800">{{ $e->parent_phone }}</p></div>
                        <div><p class="text-xs text-gray-400">Email</p><p class="text-gray-800">{{ $e->parent_email ?: '—' }}</p></div>
                    </div>
                    @if($e->message)
                    <div><p class="text-xs text-gray-400">Message</p><p class="text-sm text-gray-700 whitespace-pre-line">{{ $e->message }}</p></div>
                    @endif

                    <div class="flex flex-wrap gap-3 items-center pt-2">
                        <form method="POST" action="{{ route('admin.admissions.status', $e->id) }}" class="flex items-center gap-2">
                            @csrf
                            <select name="status" onchange="this.form.submit()"
                                    class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none">
                                <option value="new" {{ $e->status === 'new' ? 'selected' : '' }}>New</option>
                                <option value="contacted" {{ $e->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                <option value="enrolled" {{ $e->status === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                                <option value="declined" {{ $e->status === 'declined' ? 'selected' : '' }}>Declined</option>
                            </select>
                        </form>
                        <form method="POST" action="{{ route('admin.admissions.destroy', $e->id) }}"
                              onsubmit="return confirm('Delete this enquiry permanently?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs px-3 py-1.5 border border-red-200 rounded-lg text-red-500 hover:bg-red-50">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $enquiries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
