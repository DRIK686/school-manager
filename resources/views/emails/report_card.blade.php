<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
.container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.header { background: {{ $school->sidebar_color ?? '#0f766e' }}; padding: 30px; text-align: center; }
.header h1 { color: #fff; margin: 0; font-size: 20px; }
.header p { color: rgba(255,255,255,0.7); margin: 4px 0 0; font-size: 13px; }
.body { padding: 30px; }
.body p { color: #444; font-size: 14px; line-height: 1.6; margin: 0 0 16px; }
.info-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
.info-table td { padding: 8px 0; font-size: 13px; color: #555; border-bottom: 1px solid #f0f0f0; }
.info-table td:first-child { font-weight: bold; color: #333; width: 140px; }
.attach-note { background: {{ \App\Support\Theme::tint($school->sidebar_color ?? null, 0.93) }}; border: 2px dashed {{ $school->sidebar_color ?? '#0f766e' }}; border-radius: 10px; padding: 16px 20px; text-align: center; margin: 24px 0; font-size: 13px; color: #555; }
.footer { background: #f9f9f9; padding: 16px 30px; text-align: center; font-size: 11px; color: #aaa; border-top: 1px solid #eee; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $school->school_name }}</h1>
        <p>{{ $exam->name }} Report Card</p>
    </div>
    <div class="body">
        <p>Dear <strong>{{ $parent?->full_name ?? 'Parent/Guardian' }}</strong>,</p>
        <p>
            Please find attached the {{ $exam->name }} report card for your ward.
        </p>

        <table class="info-table">
            <tr>
                <td>Student Name</td>
                <td>{{ $student->full_name }}</td>
            </tr>
            <tr>
                <td>Admission No.</td>
                <td>{{ $student->admission_no }}</td>
            </tr>
            <tr>
                <td>Class</td>
                <td>{{ $student->schoolClass?->name }}</td>
            </tr>
        </table>

        <div class="attach-note">
            📎 The full report card is attached to this email as a PDF.
        </div>

        <p style="font-size:12px;color:#aaa;text-align:center">
            If you have any questions about this report, please contact the school office.
        </p>
    </div>
    <div class="footer">
        © {{ date('Y') }} {{ $school->school_name }}
        @if($contactPhone) &nbsp;·&nbsp; {{ $contactPhone }} @endif
        @if($contactEmail) &nbsp;·&nbsp; {{ $contactEmail }} @endif
    </div>
</div>
</body>
</html>
