<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'وريد' }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Tahoma,'Segoe UI',Arial,sans-serif;color:#1a2547;line-height:1.8;">
    <div style="max-width:600px;margin:0 auto;padding:24px 16px;">
        {{-- الترويسة --}}
        <div style="background:linear-gradient(120deg,#3b82f6,#8b5cf6 50%,#2dd4bf);border-radius:16px 16px 0 0;padding:28px 24px;text-align:center;">
            <div style="color:#ffffff;font-size:24px;font-weight:bold;letter-spacing:1px;">وريد</div>
            <div style="color:#eaf0fb;font-size:12px;margin-top:4px;">شركة وريد لتقنية المعلومات</div>
        </div>

        {{-- المحتوى --}}
        <div style="background:#ffffff;border:1px solid #e6ecf7;border-top:none;border-radius:0 0 16px 16px;padding:30px 26px;">
            @yield('content')
        </div>

        {{-- التذييل --}}
        <p style="text-align:center;color:#8a99bb;font-size:11px;margin:18px 0 0;">
            © {{ date('Y') }} شركة وريد لتقنية المعلومات — <a href="https://wareed.vip" style="color:#3b82f6;text-decoration:none;">wareed.vip</a>
        </p>
    </div>
</body>
</html>
