@php
    $sym  = $school->currency_symbol;
    $fmt  = fn($n) => $sym . number_format((float) $n, 2);
    $link = function ($n, $params = []) {
        try { return \Illuminate\Support\Facades\Route::has($n) ? route($n, $params) : '#'; }
        catch (\Throwable $e) { return '#'; }
    };
    $m    = $fin['monthly'];
    $n    = count($m['income']);
    $curInc = $m['income'][$n-1];  $prevInc = $n > 1 ? $m['income'][$n-2] : 0;
    $curExp = $m['expense'][$n-1]; $prevExp = $n > 1 ? $m['expense'][$n-2] : 0;
    $curNet = $curInc - $curExp;
    $pct = fn($c, $p) => $p > 0 ? round(($c - $p) / $p * 100) : null;
    $dInc = $pct($curInc, $prevInc); $dExp = $pct($curExp, $prevExp);
    $totalBalance = $fin['accounts']->sum('current_balance');
    $fees = $fin['fees'];
    $palette = ['#2563eb', '#d97706', '#059669', '#7c3aed', '#db2777', '#0891b2', '#6b7280'];
    $catColors = array_slice($palette, 0, max(1, count($fin['expenses']['rows'])));
@endphp

<div class="flex items-center justify-between mb-3">
    <h3 class="text-sm font-semibold text-gray-700">Finance at a glance &middot; {{ now()->format('F Y') }}</h3>
    <a href="{{ $link('admin.finance.index') }}" class="text-xs font-medium" style="color:var(--sidebar-bg)">Open Finance &rarr;</a>
</div>

{{-- KPI tiles --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Income this month</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $fmt($curInc) }}</p>
        <p class="text-xs text-gray-400 mt-1">@if($dInc !== null){{ $dInc >= 0 ? '▲' : '▼' }} {{ abs($dInc) }}% vs last month @else Fees + other income @endif</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Expenses this month</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $fmt($curExp) }}</p>
        <p class="text-xs text-gray-400 mt-1">@if($dExp !== null){{ $dExp >= 0 ? '▲' : '▼' }} {{ abs($dExp) }}% vs last month @else Approved expenses @endif</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">{{ $curNet >= 0 ? 'Surplus' : 'Deficit' }} this month</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $curNet < 0 ? '−' : '' }}{{ $fmt(abs($curNet)) }}</p>
        <p class="text-xs text-gray-400 mt-1">Income minus expenses</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Cash &amp; bank balance</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $fmt($totalBalance) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $fin['accounts']->count() }} account(s)</p>
    </div>
</div>

@if($fin['pending'] > 0)
<a href="{{ $link('admin.finance.transactions', ['type' => 'expense']) }}" class="block rounded-xl p-4 mb-6 border bg-yellow-50 border-yellow-200 text-sm text-yellow-800">
    <strong>{{ $fin['pending'] }}</strong> expense{{ $fin['pending'] == 1 ? '' : 's' }} waiting for approval &mdash; review now &rarr;
</a>
@endif

{{-- Charts row --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-800 text-sm">Income vs expenses</h3>
            <div class="text-xs flex gap-1">
                <a href="?months=6"  class="px-2.5 py-1 rounded-full border {{ $fin['months'] == 6  ? 'text-white' : 'text-gray-600 bg-white' }}" @if($fin['months'] == 6)  style="background:var(--sidebar-bg)" @endif>6 months</a>
                <a href="?months=12" class="px-2.5 py-1 rounded-full border {{ $fin['months'] == 12 ? 'text-white' : 'text-gray-600 bg-white' }}" @if($fin['months'] == 12) style="background:var(--sidebar-bg)" @endif>12 months</a>
            </div>
        </div>
        <div style="height:290px"><canvas id="ieChart" aria-label="Monthly income and expenses chart" role="img"></canvas></div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 text-sm mb-1">Where the money goes</h3>
        <p class="text-xs text-gray-400 mb-3">Expenses by category, last {{ $fin['months'] }} months</p>
        @if($fin['expenses']['total'] > 0)
            <div style="height:170px"><canvas id="catChart" aria-label="Expenses by category" role="img"></canvas></div>
            <ul class="mt-3 space-y-1.5">
                @foreach($fin['expenses']['rows'] as $name => $amt)
                <li class="flex items-center justify-between text-xs">
                    <span class="flex items-center gap-2 text-gray-700"><span class="inline-block w-2.5 h-2.5 rounded-sm" style="background:{{ $palette[$loop->index % count($palette)] }}"></span>{{ $name }}</span>
                    <span class="text-gray-500">{{ $fmt($amt) }} &middot; {{ round($amt / $fin['expenses']['total'] * 100) }}%</span>
                </li>
                @endforeach
            </ul>
        @else
            <div class="py-10 text-center text-gray-400 text-sm">No expenses recorded yet.</div>
        @endif
    </div>
</div>

{{-- Fees + accounts + recent --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 text-sm mb-1">Fee collection{{ $year ? ' · ' . $year->name : '' }}</h3>
        @if($fees['billed'] > 0)
            <p class="text-xs text-gray-500 mb-3">{{ $fmt($fees['paid']) }} collected of {{ $fmt($fees['billed']) }} billed</p>
            <div class="h-3 rounded-full bg-gray-100 overflow-hidden"><div class="h-3 rounded-full" style="width:{{ $fees['pct'] }}%;background:var(--sidebar-bg)"></div></div>
            <p class="text-xs text-gray-500 mt-2"><strong class="text-gray-800">{{ $fees['pct'] }}%</strong> collected &middot; {{ $fmt($fees['outstanding']) }} outstanding from {{ $fees['debtors'] }} student{{ $fees['debtors'] == 1 ? '' : 's' }}</p>
            @if(count($fees['byClass']))
            <p class="text-xs font-semibold text-gray-600 mt-4 mb-1">Highest outstanding by class</p>
            @foreach($fees['byClass'] as $c)
            <div class="flex justify-between text-xs py-1 border-b border-gray-50 last:border-0"><span class="text-gray-700">{{ $c['name'] }}</span><span class="text-gray-500">{{ $fmt($c['amount']) }}</span></div>
            @endforeach
            @endif
            <a href="{{ $link('admin.fees.balance') }}" class="inline-block text-xs font-medium mt-3" style="color:var(--sidebar-bg)">Balance report &rarr;</a>
        @else
            <div class="py-8 text-center text-gray-400 text-sm">No fee structure set for this year yet.</div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Account balances</h3>
            <a href="{{ $link('admin.finance.accounts') }}" class="text-xs font-medium" style="color:var(--sidebar-bg)">Manage &rarr;</a>
        </div>
        @forelse($fin['accounts'] as $a)
        <div class="px-5 py-3 border-b border-gray-50 last:border-0 flex items-center justify-between">
            <p class="text-sm font-medium text-gray-800">{{ $a->name }}</p>
            <p class="text-sm font-semibold text-gray-700">{{ $fmt($a->current_balance) }}</p>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-gray-400 text-sm">No accounts yet.</div>
        @endforelse
        <div class="px-5 py-3 bg-gray-50 rounded-b-xl flex gap-2 flex-wrap">
            <a href="{{ $link('admin.fees.collect') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg text-white" style="background:var(--sidebar-bg)">Collect fees</a>
            <a href="{{ $link('admin.finance.transactions', ['type' => 'expense']) }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700">Add expense</a>
            <a href="{{ $link('admin.finance.cashbook') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700">Cashbook</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Recent transactions</h3>
            <a href="{{ $link('admin.finance.cashbook') }}" class="text-xs font-medium" style="color:var(--sidebar-bg)">View all &rarr;</a>
        </div>
        @forelse($fin['recent'] as $t)
        <div class="px-5 py-3 border-b border-gray-50 last:border-0 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ $t->getAttribute('description') ?: ($t->category?->name ?? ucfirst($t->type)) }}</p>
                <p class="text-xs text-gray-400">{{ \Illuminate\Support\Carbon::parse($t->txn_date)->format('d M') }} &middot; {{ $t->account?->name }}</p>
            </div>
            <p class="text-sm font-semibold whitespace-nowrap text-gray-700">{{ $t->type === 'income' ? '+' : '−' }}{{ $fmt($t->amount) }}</p>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-gray-400 text-sm">No transactions yet.</div>
        @endforelse
    </div>
</div>

@if(!empty($fin['budget']))
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-semibold text-gray-800 text-sm">Budget watch &middot; {{ $fin['budget']['year']->name }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">
                @if($fin['budget']['flagged'] > 0)
                    {{ $fin['budget']['flagged'] }} of {{ $fin['budget']['total'] }} budget line{{ $fin['budget']['total'] == 1 ? '' : 's' }} at or above 80%
                @else
                    All {{ $fin['budget']['total'] }} budget line{{ $fin['budget']['total'] == 1 ? '' : 's' }} within limits
                @endif
            </p>
        </div>
        <a href="{{ $link('admin.finance.budget') }}" class="text-xs font-medium" style="color:var(--sidebar-bg)">Open budget &rarr;</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
        @foreach($fin['budget']['rows'] as $b)
        @php
            $barColor = $b['state'] === 'over' ? '#b91c1c' : ($b['state'] === 'near' ? '#d97706' : 'var(--sidebar-bg)');
            $label = $b['state'] === 'over' ? 'Over budget' : ($b['state'] === 'near' ? 'Near limit' : null);
        @endphp
        <div>
            <div class="flex items-center justify-between text-xs mb-1">
                <span class="font-medium text-gray-700">{{ $b['name'] }}</span>
                <span class="text-gray-500">{{ $fmt($b['spent']) }} of {{ $fmt($b['budget']) }} &middot; <strong class="text-gray-800">{{ $b['pct'] }}%</strong>@if($label) &middot; <strong style="color:{{ $barColor }}">{{ $label }}</strong>@endif</span>
            </div>
            <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-2.5 rounded-full" style="width:{{ min(100, $b['pct']) }}%;background:{{ $barColor }}"></div></div>
        </div>
        @endforeach
    </div>
</div>
@endif

<h3 class="text-sm font-semibold text-gray-700 mb-3">School at a glance</h3>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;
    var sym = @json($sym);
    var money = function (v) { return sym + Number(v).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); };
    var brand = (getComputedStyle(document.documentElement).getPropertyValue('--sidebar-bg') || '#4c1d95').trim() || '#4c1d95';
    Chart.defaults.font.family = 'inherit';

    var ie = document.getElementById('ieChart');
    if (ie) new Chart(ie, {
        data: {
            labels: @json($m['labels']),
            datasets: [
                {type: 'bar', label: 'Income', data: @json($m['income']), backgroundColor: brand, borderRadius: 4},
                {type: 'bar', label: 'Expenses', data: @json($m['expense']), backgroundColor: '#d97706', borderRadius: 4},
                {type: 'line', label: 'Net', data: @json($m['net']), borderColor: '#111827', backgroundColor: '#111827', borderWidth: 2, pointRadius: 3, tension: 0}
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: {mode: 'index', intersect: false},
            plugins: {legend: {position: 'bottom'}, tooltip: {callbacks: {label: function (c) { return c.dataset.label + ': ' + money(c.parsed.y); }}}},
            scales: {y: {beginAtZero: true, ticks: {callback: function (v) { return sym + Number(v).toLocaleString(); }}}}
        }
    });

    var cat = document.getElementById('catChart');
    if (cat) new Chart(cat, {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($fin['expenses']['rows'])),
            datasets: [{data: @json(array_values($fin['expenses']['rows'])), backgroundColor: @json($catColors), borderWidth: 2}]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '62%',
            plugins: {legend: {display: false}, tooltip: {callbacks: {label: function (c) { return c.label + ': ' + money(c.parsed); }}}}
        }
    });
})();
</script>
