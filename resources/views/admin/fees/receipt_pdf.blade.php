<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
    .header { background: #991b1b; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
    .header h1 { margin: 0; font-size: 18px; }
    .header p { margin: 2px 0; font-size: 11px; opacity: 0.8; }
    .badge { background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: bold; display: inline-block; margin-top: 8px; }
    .body { padding: 20px; border: 1px solid #eee; border-top: none; border-radius: 0 0 8px 8px; }
    .receipt-no { font-size: 18px; font-weight: bold; color: #991b1b; font-family: monospace; }
    .student-box { background: #f9fafb; border-radius: 6px; padding: 12px; margin: 15px 0; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th { text-align: left; font-size: 10px; color: #888; text-transform: uppercase; padding: 6px 0; border-bottom: 1px solid #eee; }
    td { padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
    .total { font-size: 15px; font-weight: bold; color: #991b1b; }
    .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #999; }
</style>
</head>
<body>
<div class="header">
    <h1>{{ $school->school_name }}</h1>
    <p>{{ $school->address }}</p>
    <p>{{ $school->phone }}</p>
    <span class="badge">OFFICIAL RECEIPT</span>
</div>
<div class="body">
    <table>
        <tr>
            <td><strong>Receipt No:</strong><br><span class="receipt-no">{{ $payment->receipt_no }}</span></td>
            <td style="text-align:right"><strong>Date:</strong><br>{{ $payment->payment_date->format('d M Y') }}</td>
        </tr>
    </table>

    <div class="student-box">
        <strong>{{ $payment->student->full_name }}</strong><br>
        <span style="color:#888">{{ $payment->student->admission_no }} • {{ $payment->student->schoolClass?->name }}</span><br>
        <span style="color:#888">Academic Year: {{ $payment->academicYear?->name }}</span>
    </div>

    <table>
        <thead><tr><th>Description</th><th style="text-align:right">Amount</th></tr></thead>
        <tbody>
            <tr>
                <td>{{ $payment->feeStructure->feeCategory->name }}</td>
                <td style="text-align:right">{{ $school->currency_symbol }}{{ number_format($payment->amount_paid,2) }}</td>
            </tr>
            @if($payment->fine_paid > 0)
            <tr><td style="color:red">Late Fine</td><td style="text-align:right;color:red">{{ $school->currency_symbol }}{{ number_format($payment->fine_paid,2) }}</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td><strong>Total Paid</strong></td>
                <td style="text-align:right" class="total">{{ $school->currency_symbol }}{{ number_format($payment->amount_paid + $payment->fine_paid,2) }}</td>
            </tr>
        </tfoot>
    </table>

    <p><strong>Payment Mode:</strong> {{ ucfirst(str_replace('_',' ',$payment->payment_mode)) }}</p>
    @if($payment->reference_no)<p><strong>Reference:</strong> {{ $payment->reference_no }}</p>@endif

    <div class="footer">
        <p>Collected by: {{ $payment->collectedBy?->name }}</p>
        <p>Thank you for your payment!</p>
    </div>
</div>
</body>
</html>
