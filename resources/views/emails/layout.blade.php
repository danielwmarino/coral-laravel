<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Coral' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 560px; margin: 40px auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .header { background: #003470; padding: 32px 40px; }
        .header-logo { color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
        .header-logo span { color: #FC54AA; }
        .body { padding: 40px; }
        .body h1 { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 12px; line-height: 1.3; }
        .body p { font-size: 15px; color: #4b5563; line-height: 1.7; margin-bottom: 16px; }
        .body p:last-child { margin-bottom: 0; }
        .btn { display: inline-block; margin: 24px 0; padding: 13px 28px; background: #FC54AA; color: #ffffff !important; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 600; }
        .divider { border: none; border-top: 1px solid #f3f4f6; margin: 28px 0; }
        .detail-box { background: #f9fafb; border-radius: 10px; padding: 18px 22px; margin: 20px 0; }
        .detail-box p { font-size: 14px; color: #374151; margin-bottom: 6px; }
        .detail-box p:last-child { margin-bottom: 0; }
        .detail-box strong { color: #111827; }
        .footer { padding: 24px 40px; }
        .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="header-logo">coral<span>.</span></div>
            </div>
            <div class="body">
                {{ $slot }}
            </div>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Coral by Raka Creative. You're receiving this because an account was created for you.<br>
            If you didn't expect this email, you can safely ignore it.</p>
        </div>
    </div>
</body>
</html>
