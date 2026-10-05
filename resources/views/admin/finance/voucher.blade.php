<!doctype html><html><head><meta charset="utf-8"><style>
body{font-family:'DejaVu Sans',sans-serif;font-size:11px;color:#111}
h1{font-size:15px;margin:0}.c{text-align:center}table{width:100%;border-collapse:collapse;margin-top:10px}
td{padding:6px 4px;border-bottom:1px solid #ddd;vertical-align:top}.k{width:32%;color:#555}
.amt{font-size:16px;font-weight:bold}.sig{margin-top:38px}.sig td{border:none;text-align:center;padding-top:26px}
.line{border-top:1px solid #333;padding-top:3px;font-size:10px}
</style></head><body>
<div class="c"><h1>{{ $school->school_name }}</h1><div>PAYMENT VOUCHER</div></div>
<table>
<tr><td class="k">Voucher No.</td><td>VCH-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
<tr><td class="k">Date</td><td>{{ $transaction->txn_date->format('d M Y') }}</td></tr>
<tr><td class="k">Paid to</td><td>{{ $transaction->payee ?: '—' }}</td></tr>
<tr><td class="k">Category</td><td>{{ $transaction->category->name ?? '' }}</td></tr>
<tr><td class="k">Description</td><td>{{ $transaction->description ?: '—' }}</td></tr>
<tr><td class="k">Paid from</td><td>{{ $transaction->account->name ?? '' }}</td></tr>
<tr><td class="k">Reference</td><td>{{ $transaction->reference_no ?: '—' }}</td></tr>
<tr><td class="k">Amount</td><td class="amt">{{ $school->currency_symbol }}{{ number_format($transaction->amount, 2) }}</td></tr>
</table>
<table class="sig"><tr>
<td><div class="line">Prepared by<br>{{ $transaction->creator->name ?? '' }}</div></td>
<td><div class="line">Approved by</div></td>
<td><div class="line">Received by (sign &amp; date)</div></td>
</tr></table>
</body></html>
