<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:32px 16px;">
<tr>
<td align="center">
<table role="presentation" width="100%" style="max-width:480px; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);">

    {{-- Header --}}
    <tr>
        <td align="center" style="background-color:{{ $school->theme_color ?? '#0f766e' }}; padding:40px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
                <tr><td align="center" style="width:64px; height:64px; background-color:#ffffff; border-radius:50%;">
                    @if($school && $school->logo)
                    <img src="{{ url('/storage/'.$school->logo) }}" width="64" height="64" style="border-radius:50%; object-fit:cover; display:block;">
                    @else
                    <div style="width:64px; height:64px; line-height:64px; text-align:center; font-size:28px; font-weight:bold; color:{{ $school->theme_color ?? '#0f766e' }};">
                        {{ strtoupper(substr($school->school_name ?? 'S', 0, 1)) }}
                    </div>
                    @endif
                </td></tr>
            </table>
            <h1 style="color:#ffffff; font-size:22px; margin:20px 0 4px;">{{ $school->school_name ?? 'SchoolManager' }}</h1>
            <p style="color:rgba(255,255,255,0.75); font-size:14px; margin:0;">Staff Portal</p>
        </td>
    </tr>

    {{-- Body --}}
    <tr>
        <td style="padding:32px 28px;">
            <h2 style="font-size:18px; color:#1f2937; margin:0 0 16px;">Welcome, {{ $newUser->name }}!</h2>
            <p style="font-size:14px; color:#4b5563; line-height:1.6; margin:0 0 16px;">
                An account has been created for you on {{ $school->school_name ?? 'the' }} staff portal.
            </p>
            <table role="presentation" width="100%" style="background-color:#f9fafb; border-radius:10px; margin:0 0 24px;">
                <tr><td style="padding:14px 18px;">
                    <p style="font-size:12px; color:#9ca3af; margin:0 0 2px; text-transform:uppercase; letter-spacing:0.03em;">Login Email</p>
                    <p style="font-size:14px; color:#1f2937; margin:0; font-weight:600;">{{ $newUser->email }}</p>
                </td></tr>
            </table>
            <p style="font-size:14px; color:#4b5563; line-height:1.6; margin:0 0 24px;">
                Click below to set your password and get started. This link expires in 48 hours.
            </p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 8px;">
                <tr><td align="center" style="background-color:{{ $school->theme_color ?? '#0f766e' }}; border-radius:10px;">
                    <a href="{{ $resetUrl }}" style="display:inline-block; padding:13px 32px; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none;">
                        Set Your Password
                    </a>
                </td></tr>
            </table>
            <p style="font-size:12px; color:#9ca3af; text-align:center; margin:20px 0 0;">
                If the button doesn't work, copy and paste this link:<br>
                <span style="word-break:break-all;">{{ $resetUrl }}</span>
            </p>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td align="center" style="padding:20px 24px; border-top:1px solid #f3f4f6;">
            <p style="font-size:12px; color:#9ca3af; margin:0;">&copy; {{ date('Y') }} {{ $school->school_name ?? config('app.name', 'SchoolManager') }}. All rights reserved.</p>
        </td>
    </tr>

</table>
</td>
</tr>
</table>
</body>
</html>
