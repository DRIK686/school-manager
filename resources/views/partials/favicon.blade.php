@php
    $__fs      = $school ?? \App\Models\SchoolSetting::first();
    $__logo    = $__fs->logo ?? null;
    $__hasLogo = $__logo && file_exists(public_path('storage/' . $__logo));
    $__favFile = $__hasLogo ? \App\Support\Favicon::forLogo($__logo) : null;
    $__favHref = asset('storage/' . ($__favFile ?? $__logo));
    $__color   = $__fs->theme_color ?? '#0f766e';
    if (! preg_match('/^#[0-9a-fA-F]{6}$/', (string) $__color)) { $__color = '#0f766e'; }
    $__name    = trim((string) ($__fs->school_name ?? ''));
    $__letter  = $__name !== '' ? mb_strtoupper(mb_substr($__name, 0, 1)) : 'S';
    $__svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="' . $__color . '"/><text x="32" y="45" font-family="Arial,Helvetica,sans-serif" font-size="38" font-weight="700" text-anchor="middle" fill="#ffffff">' . e($__letter) . '</text></svg>';
@endphp
@if($__hasLogo)
<link rel="icon" type="image/png" href="{{ $__favHref }}">
<link rel="apple-touch-icon" href="{{ $__favHref }}">
@else
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,{{ rawurlencode($__svg) }}">
@endif
