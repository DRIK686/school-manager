@extends('layouts.admin')
@section('title', $staff->name)
@section('content')

<div class="mb-4 flex items-center justify-between">
    <a href="{{ route('admin.staff.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Staff
    </a>
    <div class="flex gap-2">
        <a href="{{ route('admin.staff.edit', $staff) }}"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">Edit</a>
        <form method="POST" action="{{ route('admin.staff.destroy', $staff) }}"
            onsubmit="return confirm('Remove this staff member?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-sm px-4 py-2 border border-red-100 rounded-lg hover:bg-red-50 text-red-500">Remove</button>
        </form>
    </div>
</div>

{{-- Header --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center gap-5">
        @if($staff->profile_photo)
            <img src="{{ asset('storage/'.$staff->profile_photo) }}"
                class="w-20 h-20 rounded-xl object-cover border-2 border-gray-100 flex-shrink-0">
        @else
            <div class="w-20 h-20 rounded-xl flex items-center justify-center text-white text-2xl font-bold flex-shrink-0"
                style="background:var(--sidebar-bg)">
                {{ strtoupper(substr($staff->name,0,1)) }}
            </div>
        @endif
        <div class="flex-1">
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-xl font-bold text-gray-800">{{ $staff->name }}</h2>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">{{ $staff->role?->name }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $staff->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $staff->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs">{{ $staff->staffProfile?->employee_id ?? '—' }}</span>
                &nbsp;•&nbsp; {{ $staff->staffProfile?->designation ?? 'No designation' }}
                &nbsp;•&nbsp; {{ $staff->email }}
            </p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        {{-- Employment --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Employment Details</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                @foreach([
                    ['Department', $staff->staffProfile?->department ?? '—'],
                    ['Designation', $staff->staffProfile?->designation ?? '—'],
                    ['Joining Date', $staff->staffProfile?->joining_date?->format('d M Y') ?? '—'],
                    ['Qualification', $staff->staffProfile?->qualification ?? '—'],
                    ['Experience', $staff->staffProfile?->experience ?? '—'],
                    ['Basic Salary', ($staff->staffProfile?->basic_salary ? \App\Models\SchoolSetting::current()->currency_symbol . number_format($staff->staffProfile->basic_salary,2) : '—')],
                ] as [$label,$value])
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">{{ $label }}</p>
                    <p class="font-medium text-gray-700">{{ $value }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Personal --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Personal Details</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                @foreach([
                    ['Gender', ucfirst($staff->staffProfile?->gender ?? '—')],
                    ['Date of Birth', $staff->staffProfile?->dob?->format('d M Y') ?? '—'],
                    ['Blood Group', $staff->staffProfile?->blood_group ?? '—'],
                    ['Marital Status', ucfirst($staff->staffProfile?->marital_status ?? '—')],
                    ['Phone', $staff->staffProfile?->phone ?? $staff->phone ?? '—'],
                    ['Emergency Contact', $staff->staffProfile?->emergency_contact ?? '—'],
                    ['Address', $staff->staffProfile?->address ?? '—'],
                    ['Bank Name', $staff->staffProfile?->bank_name ?? '—'],
                    ['Account No', $staff->staffProfile?->bank_account ?? '—'],
                ] as [$label,$value])
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">{{ $label }}</p>
                    <p class="font-medium text-gray-700">{{ $value }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Documents --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Documents</h3>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                <form method="POST" action="{{ route('admin.staff.documents.upload', $staff) }}"
                    enctype="multipart/form-data" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div class="flex-1 min-w-36">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Document Type</label>
                        <select name="document_type" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                            @foreach(['National ID','Certificate','CV/Resume','Contract','Transcript','Other'] as $dt)
                                <option>{{ $dt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-600 mb-1">File</label>
                        <input type="file" name="document_file" required
                            class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-gray-100 file:text-gray-700">
                    </div>
                    <button type="submit" class="btn-primary px-4 py-2 text-sm">Upload</button>
                </form>
            </div>
            @forelse($staff->staffDocuments as $doc)
            <div class="flex items-center justify-between px-6 py-3 border-b border-gray-50 hover:bg-gray-50">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $doc->document_type }}</p>
                        <p class="text-xs text-gray-400">{{ $doc->file_name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank"
                        class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">View</a>
                    <form method="POST" action="{{ route('admin.staff.documents.delete', [$staff, $doc]) }}"
                        onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-6 py-6 text-center text-gray-400 text-sm">No documents uploaded.</div>
            @endforelse
        </div>
    </div>

    {{-- RIGHT: Quick Info --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Quick Info</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Employee ID</span>
                    <span class="font-mono font-medium">{{ $staff->staffProfile?->employee_id ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Role</span>
                    <span class="font-medium">{{ $staff->role?->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Joined</span>
                    <span class="font-medium">{{ $staff->staffProfile?->joining_date?->format('d M Y') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Documents</span>
                    <span class="font-medium">{{ $staff->staffDocuments->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
