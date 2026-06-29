@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 10px;font-size:20px;color:#1a2547;">شكراً {{ $sr->name }} 🙏</h2>
<p style="margin:0 0 14px;color:#1a2547;font-size:15px;">تم استلام طلبك بنجاح، وفريق <strong>شركة وريد لتقنية المعلومات</strong> سيتواصل معك في أقرب وقت.</p>
<p style="margin:0 0 20px;color:#65718f;font-size:14px;">نحن سعداء بثقتك، ونعدك بتجربة تقنية احترافية تليق بطموحك.</p>

<div style="background:#f4f6fb;border-radius:12px;padding:16px 18px;margin-bottom:22px;">
    <div style="color:#65718f;font-size:13px;margin-bottom:4px;">ملخّص طلبك</div>
    <div style="color:#1a2547;font-size:14px;font-weight:600;">{{ $sr->message ? \Illuminate\Support\Str::limit($sr->message, 160) : 'سيتم التواصل معك لمناقشة التفاصيل.' }}</div>
</div>

<div style="text-align:center;">
    <a href="https://wareed.vip" style="display:inline-block;background:linear-gradient(120deg,#3b82f6,#8b5cf6 60%,#2dd4bf);color:#fff;text-decoration:none;font-weight:bold;padding:12px 26px;border-radius:10px;font-size:14px;">تصفّح خدمات وريد</a>
</div>

<p style="margin:22px 0 0;color:#8a99bb;font-size:12px;text-align:center;">إن لم تكن أنت من أرسل هذا الطلب، تجاهل هذه الرسالة.</p>
@endsection
