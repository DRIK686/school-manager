<div class="flex flex-wrap gap-2 mb-6 text-sm">
    @foreach([
        ['admin.finance.index','Overview'],
        ['admin.finance.cashbook','Cash Book'],
        ['admin.finance.statement','Income & Expenditure'],
        ['admin.finance.budget','Budget'],
        ['admin.finance.accounts','Accounts & Transfers'],
    ] as [$r,$l])
        <a href="{{ route($r) }}" class="px-3 py-1.5 rounded-lg border {{ request()->routeIs($r) ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border-gray-200' }}">{{ $l }}</a>
    @endforeach
    <a href="{{ route('admin.finance.transactions','expense') }}" class="px-3 py-1.5 rounded-lg border {{ request()->is('admin/finance/transactions/expense') ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border-gray-200' }}">Expenses</a>
    <a href="{{ route('admin.finance.transactions','income') }}" class="px-3 py-1.5 rounded-lg border {{ request()->is('admin/finance/transactions/income') ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border-gray-200' }}">Other Income</a>
    <a href="{{ route('admin.finance.categories') }}" class="px-3 py-1.5 rounded-lg border {{ request()->routeIs('admin.finance.categories') ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border-gray-200' }}">Categories</a>
    <a href="{{ route('admin.fees.discounts') }}" class="px-3 py-1.5 rounded-lg border bg-white text-gray-700 border-gray-200">Discounts</a>
    <a href="{{ route('admin.fees.balance') }}" class="px-3 py-1.5 rounded-lg border bg-white text-gray-700 border-gray-200">Debtors</a>
    <a href="{{ route('admin.fees.report') }}" class="px-3 py-1.5 rounded-lg border bg-white text-gray-700 border-gray-200">Fee Report</a>
    <a href="{{ route('admin.fees.collect') }}" class="px-3 py-1.5 rounded-lg border bg-white text-gray-700 border-gray-200">Collect Fees →</a>
</div>
