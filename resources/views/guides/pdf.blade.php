@php
    $c    = \App\Support\Theme::primary();
    $on   = \App\Support\Theme::readable($c);
    $tint = \App\Support\Theme::tint($c, 0.92);
@endphp
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $title }}</title>
<style>
@page { margin: 70px 55px 70px 55px; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; line-height: 1.55; color: #1f2937; }
.band { background: {{ $c }}; color: {{ $on }}; padding: 22px 26px; margin-bottom: 16px; }
.band .school { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
.band .name { font-size: 22px; font-weight: bold; margin-top: 4px; }
.band .date { font-size: 9px; margin-top: 6px; }
.contents { background: {{ $tint }}; padding: 10px 18px; margin-bottom: 18px; }
.contents b { font-size: 11px; }
.contents ol { margin: 6px 0 0 0; padding-left: 18px; }
.contents li { margin: 2px 0; }
h2 { font-size: 15px; color: #111827; border-bottom: 2px solid {{ $c }}; padding-bottom: 3px; margin: 22px 0 8px; page-break-after: avoid; }
h3 { font-size: 12px; color: #111827; margin: 14px 0 5px; page-break-after: avoid; }
p { margin: 0 0 8px; }
ul, ol { margin: 0 0 9px 0; padding-left: 20px; }
li { margin: 2px 0; page-break-inside: avoid; }
table { width: 100%; border-collapse: collapse; margin: 0 0 10px; }
th { background: {{ $tint }}; text-align: left; }
th, td { border: 1px solid #d1d5db; padding: 4px 6px; vertical-align: top; font-size: 9.5px; }
tr { page-break-inside: avoid; }
code { font-family: 'DejaVu Sans Mono', monospace; font-size: 9px; background: #f3f4f6; }
blockquote { margin: 0 0 9px; padding: 6px 10px; background: {{ $tint }}; border-left: 3px solid {{ $c }}; }
blockquote p { margin: 0; }
.footer { position: fixed; bottom: -42px; left: 0; right: 0; text-align: center; font-size: 8px; color: #6b7280; }
</style>
</head>
<body>
<div class="footer">{{ $schoolName }} &middot; {{ $title }}</div>

<div class="band">
    <div class="school">{{ $schoolName }}</div>
    <div class="name">{{ $title }}</div>
    <div class="date">Generated {{ now()->format('j F Y') }}</div>
</div>

@if(count($toc))
<div class="contents">
    <b>Contents</b>
    <ol>
        @foreach($toc as $t)
            <li>{{ $t['text'] }}</li>
        @endforeach
    </ol>
</div>
@endif

{!! $html !!}
</body>
</html>
