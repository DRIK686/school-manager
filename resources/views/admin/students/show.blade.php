@extends('layouts.admin')
@section('title', $student->full_name)
@section('content')

<div class="mb-4 flex items-center justify-between">
    <a href="{{ route('admin.students.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Students
    </a>
    <div class="flex gap-2">
        <a href="{{ route('admin.students.edit', $student) }}"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition">Edit</a>
        @if($student->is_active)
        <form method="POST" action="{{ route('admin.students.withdraw', $student) }}" id="withdraw-form"
            onsubmit="return handleWithdrawSubmit(event)">
            @csrf
            <input type="hidden" name="reason" id="withdraw-reason">
            <button type="submit" class="text-sm px-4 py-2 border border-amber-200 rounded-lg hover:bg-amber-50 text-amber-600 transition">Withdraw</button>
        </form>
        <script>
        function handleWithdrawSubmit(e) {
            if (!confirm('Mark {{ $student->full_name }} as withdrawn? This keeps their record but removes them from active lists, attendance, and fees.')) {
                return false;
            }
            const reason = prompt('Reason for withdrawal (optional) — e.g. relocated, transferred to another school:');
            if (reason === null) {
                return false; // user hit Cancel on the prompt
            }
            document.getElementById('withdraw-reason').value = reason;
            return true;
        }
        </script>
        @else
        <form method="POST" action="{{ route('admin.students.reinstate', $student) }}"
            onsubmit="return confirm('Reinstate {{ $student->full_name }}? They will reappear in active lists, attendance, and fees.')">
            @csrf
            <button type="submit" class="text-sm px-4 py-2 border border-green-200 rounded-lg hover:bg-green-50 text-green-600 transition">Reinstate</button>
        </form>
        @endif
        <form method="POST" action="{{ route('admin.students.destroy', $student) }}"
            onsubmit="return confirm('Remove this student?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-sm px-4 py-2 border border-red-100 rounded-lg hover:bg-red-50 text-red-500 transition">Remove</button>
        </form>
    </div>
</div>

{{-- Profile Header --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center gap-6">
        @if($student->profile_photo)
            <img src="{{ asset('storage/'.$student->profile_photo) }}"
                class="w-20 h-20 rounded-xl object-cover border-2 border-gray-100 flex-shrink-0">
        @else
            <div class="w-20 h-20 rounded-xl flex items-center justify-center text-white text-2xl font-bold flex-shrink-0"
                style="background:{{ $student->gender === 'male' ? 'var(--sidebar-bg)' : '#e11d48' }}">
                {{ strtoupper(substr($student->first_name,0,1)) }}
            </div>
        @endif
        <div class="flex-1">
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-xl font-bold text-gray-800">{{ $student->full_name }}</h2>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $student->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $student->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs">{{ $student->admission_no }}</span>
                &nbsp;•&nbsp; {{ $student->schoolClass?->name ?? '—' }}
                @if($student->section) / Section {{ $student->section->name }} @endif
                &nbsp;•&nbsp; {{ $student->academicYear?->name ?? '—' }}
                &nbsp;•&nbsp;
                <a href="{{ route('admin.students.history', $student) }}" class="text-primary hover:underline text-xs font-semibold">
                    📅 View Academic History
                </a>
            </p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Details --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Personal --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Personal Details</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                @foreach([
                    ['Gender', ucfirst($student->gender)],
                    ['Date of Birth', $student->dob?->format('d M Y') ?? '—'],
                    ['Blood Group', $student->blood_group ?? '—'],
                    ['Religion', $student->religion ?? '—'],
                    ['Nationality', $student->nationality ?? '—'],
                    ['Phone', $student->phone ?? '—'],
                    ['Address', $student->address ?? '—'],
                    ['Previous School', $student->previous_school ?? '—'],
                    ['Previous Class', $student->previous_class ?? '—'],
                ] as [$label, $value])
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
                <form method="POST" action="{{ route('admin.students.documents.upload', $student) }}" enctype="multipart/form-data"
                    class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div class="flex-1 min-w-36">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Document Type</label>
                        <select name="document_type" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                            @foreach(['Birth Certificate','Report Card','Passport Photo','Medical Record','Transfer Certificate','Other'] as $dt)
                                <option value="{{ $dt }}">{{ $dt }}</option>
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
            @forelse($student->documents as $doc)
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
                    <form method="POST" action="{{ route('admin.students.documents.delete', [$student, $doc]) }}"
                        onsubmit="return confirm('Delete this document?')">
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

    {{-- RIGHT: Parents --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Parent / Guardian</h3>
            </div>
            @forelse($student->parents as $parent)
            <div class="px-6 py-4 border-b border-gray-50">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ ucfirst($parent->relation) }}</span>
                </div>
                <p class="text-sm font-semibold text-gray-800">{{ $parent->full_name }}</p>
                @if($parent->phone)<p class="text-xs text-gray-500 mt-0.5">📞 {{ $parent->phone }}</p>@endif
                @if($parent->email)<p class="text-xs text-gray-500">✉ {{ $parent->email }}</p>@endif
                @if($parent->occupation)<p class="text-xs text-gray-500">💼 {{ $parent->occupation }}</p>@endif
            </div>
            @empty
            <div class="px-6 py-6 text-center text-gray-400 text-sm">No parent info.</div>
            @endforelse
        </div>

        {{-- Admission Summary --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Admission Summary</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Admitted On</span>
                    <span class="font-medium">{{ $student->admission_date->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Roll No</span>
                    <span class="font-medium">{{ $student->roll_no ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Documents</span>
                    <span class="font-medium">{{ $student->documents->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
