@php
    $c    = \App\Support\Theme::primary();
    $hx   = ltrim($c, '#');
    if (strlen($hx) === 3) { $hx = $hx[0].$hx[0].$hx[1].$hx[1].$hx[2].$hx[2]; }
    [$r, $g, $b] = array_map('hexdec', str_split(substr($hx, 0, 6), 2));
    $on   = (0.299 * $r + 0.587 * $g + 0.114 * $b) > 150 ? '#111827' : '#ffffff';
    $tint = \App\Support\Theme::tint($c, 0.92);
@endphp
<style>
.guide-bar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;background:{{ $c }};color:{{ $on }};border-radius:14px;padding:18px 22px;margin-bottom:20px}
.guide-bar h2{margin:0;font-size:20px;font-weight:700}
.guide-bar p{margin:2px 0 0;font-size:13px;opacity:.85}
.guide-btn{display:inline-block;background:{{ $on }};color:{{ $c }};font-weight:600;font-size:14px;padding:9px 16px;border-radius:9px;text-decoration:none}
.guide-wrap{display:flex;gap:24px;align-items:flex-start}
.guide-toc{width:230px;flex-shrink:0;position:sticky;top:16px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px;max-height:calc(100vh - 40px);overflow:auto}
.guide-toc-title{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#6b7280;margin-bottom:8px}
.guide-toc a{display:block;font-size:13px;color:#374151;text-decoration:none;padding:5px 8px;border-radius:6px}
.guide-toc a:hover{background:{{ $tint }}}
.guide-body{flex:1;min-width:0;max-width:860px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:28px 32px;line-height:1.65;color:#374151;font-size:15px}
.guide-body h1{display:none}
.guide-body h2{scroll-margin-top:80px;font-size:22px;font-weight:700;color:#111827;margin:36px 0 12px;padding-left:12px;border-left:4px solid {{ $c }}}
.guide-body h2:first-of-type{margin-top:0}
.guide-body h3{scroll-margin-top:80px;font-size:16px;font-weight:700;color:#111827;margin:24px 0 8px}
.guide-body p{margin:0 0 12px}
.guide-body ul,.guide-body ol{margin:0 0 14px;padding-left:24px}
.guide-body ul{list-style:disc}
.guide-body ol{list-style:decimal}
.guide-body li{margin:4px 0}
.guide-body li input{margin-right:6px}
.guide-body table{width:100%;border-collapse:collapse;margin:0 0 16px;font-size:14px}
.guide-body th{background:{{ $tint }};text-align:left;font-weight:600;color:#111827}
.guide-body th,.guide-body td{border:1px solid #e5e7eb;padding:8px 10px;vertical-align:top}
.guide-body code{background:#f3f4f6;border-radius:4px;padding:1px 5px;font-size:13px}
.guide-body blockquote{margin:0 0 14px;padding:10px 14px;background:{{ $tint }};border-left:4px solid {{ $c }};border-radius:6px}
.guide-body blockquote p:last-child{margin:0}
.guide-body a{color:#2563eb}
@media (max-width:1023px){.guide-wrap{display:block}.guide-toc{display:none}.guide-body{padding:20px}}
@media print{.guide-bar .guide-btn,.guide-toc{display:none}}
</style>

<div class="guide-bar">
    <div>
        <h2>{{ $title }}</h2>
        <p>{{ $school->school_name ?? config('app.name') }}</p>
    </div>
    <a class="guide-btn" href="{{ $pdfUrl }}">Download PDF</a>
</div>

<div class="guide-wrap">
    @if(count($toc))
    <nav class="guide-toc">
        <div class="guide-toc-title">On this page</div>
        @foreach($toc as $t)
            <a href="#{{ $t['id'] }}">{{ $t['text'] }}</a>
        @endforeach
    </nav>
    @endif
    <article class="guide-body">{!! $html !!}</article>
</div>
