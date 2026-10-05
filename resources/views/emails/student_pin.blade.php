<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
.container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.header { background: {{ $school->sidebar_color ?? '#7f1d1d' }}; padding: 30px; text-align: center; }
.header h1 { color: #fff; margin: 0; font-size: 20px; }
.header p { color: rgba(255,255,255,0.7); margin: 4px 0 0; font-size: 13px; }
.body { padding: 30px; }
.body p { color: #444; font-size: 14px; line-height: 1.6; margin: 0 0 16px; }
.pin-box { background: #f8f4f4; border: 2px dashed {{ $school->sidebar_color ?? '#7f1d1d' }}; border-radius: 10px; padding: 20px; text-align: center; margin: 24px 0; }
.pin-box .label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
.pin-box .pin { font-size: 36px; font-weight: bold; color: {{ $school->sidebar_color ?? '#7f1d1d' }}; letter-spacing: 6px; margin: 8px 0 0; font-family: monospace; }
.info-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
.info-table td { padding: 8px 0; font-size: 13px; color: #555; border-bottom: 1px solid #f0f0f0; }
.info-table td:first-child { font-weight: bold; color: #333; width: 140px; }
.login-btn { display: block; text-align: center; background: {{ $school->sidebar_color ?? '#7f1d1d' }}; color: #fff; text-decoration: none; padding: 14px 24px; border-radius: 8px; font-size: 14px; font-weight: bold; margin: 24px 0 8px; }
.footer { background: #f9f9f9; padding: 16px 30px; text-align: center; font-size: 11px; color: #aaa; border-top: 1px solid #eee; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $school->school_name }}</h1>
        <p>Student Portal Access</p>
    </div>
    <div class="body">
        <p>Dear <strong>{{ $parent?->full_name ?? 'Parent/Guardian' }}</strong>,</p>
        <p>
            Your ward's student portal access credentials have been set up.
            Please use the details below to log in to the student portal.
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

        <div class="pin-box">
            <div class="label">Your Login PIN</div>
            <div class="pin">{{ $pin }}</div>
        </div>

        <p style="font-size:13px;color:#888">
            Use your <strong>Admission Number</strong> and this <strong>PIN</strong> to sign in.
            You will be asked to change this PIN on your first login.
        </p>

        <a href="https://{{ request()->getHost() }}/student/login" class="login-btn">
            Sign In to Student Portal →
        </a>

        <p style="font-size:12px;color:#aaa;text-align:center">
            If you did not expect this email, please contact the school office.
        </p>
    </div>
    <div class="footer">
        © {{ date('Y') }} {{ $school->school_name }} &nbsp;·&nbsp;
        {{ $school->phone }} &nbsp;·&nbsp; {{ $school->email }}
    </div>
</div>
</body>
</html>
