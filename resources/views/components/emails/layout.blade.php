<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coral</title>
    <style>
        body { margin: 0; padding: 0; background: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #111827; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .header { background: #003470; padding: 24px 32px; }
        .header img { height: 32px; width: auto; }
        .header-text { color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: -0.3px; }
        .body { padding: 32px; }
        h1 { font-size: 20px; font-weight: 700; color: #003470; margin: 0 0 16px; }
        p { font-size: 15px; line-height: 1.6; color: #374151; margin: 0 0 14px; }
        a.btn { display: inline-block; margin: 8px 0 20px; padding: 12px 24px; background: #FC54AA; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600; }
        hr.divider { border: none; border-top: 1px solid #f3f4f6; margin: 24px 0; }
        .detail-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .detail-box p { margin: 0 0 6px; font-size: 14px; color: #374151; }
        .detail-box p:last-child { margin: 0; }
        .footer { padding: 20px 32px; background: #f9fafb; border-top: 1px solid #f3f4f6; }
        .footer p { font-size: 12px; color: #9ca3af; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <span class="header-text">coral</span>
        </div>
        <div class="body">
            {{ $slot }}
        </div>
        <div class="footer">
            <p>You're receiving this because you have an account on Coral. © {{ date('Y') }} Coral.</p>
        </div>
    </div>
</body>
</html>
