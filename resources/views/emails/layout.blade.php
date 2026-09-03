{{--
    قالب البريد الموحّد لوريد — وضع فاتح دائماً وجداول متداخلة لأوسع توافق مع عملاء البريد
    (Gmail وOutlook لا يدعمان flex/grid ولا صور SVG، لذا: جداول وصورة PNG).
--}}
@php
    $mailPhone = setting('contact_phone', '01055789056');
    $mailEmail = setting('contact_email', 'info@wareed.vip');
    $mailLegalName = setting('legal_name', 'شركة وريد لتقنية المعلومات');
    $mailAddress = setting('legal_address', setting('contact_address'));
    $mailTax = setting('tax_number');
    $mailRegister = setting('commercial_register');
@endphp
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $title ?? 'وريد' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#eef2f9;color:#0d1830;
             font-family:Tahoma,'Segoe UI',Arial,sans-serif;line-height:1.9;
             -webkit-font-smoothing:antialiased;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
       style="background-color:#eef2f9;">
    <tr>
        <td align="center" style="padding:28px 14px 40px;">

            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="width:100%;max-width:600px;background-color:#ffffff;
                          border:1px solid #e3eaf6;border-radius:16px;overflow:hidden;">

                {{-- شريط الهوية المتدرّج --}}
                <tr>
                    <td style="height:5px;line-height:5px;font-size:0;
                               background-color:#8b5cf6;
                               background-image:linear-gradient(90deg,#3b82f6,#8b5cf6 50%,#2dd4bf);">&nbsp;</td>
                </tr>

                {{-- الترويسة: الشعار واسم الشركة --}}
                <tr>
                    <td style="padding:22px 26px 18px;border-bottom:1px solid #eef2fa;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="padding-inline-end:12px;" valign="middle">
                                    <img src="{{ url('/images/wareed-mark.png') }}" width="44" height="44"
                                         alt="وريد"
                                         style="display:block;width:44px;height:44px;border:0;border-radius:11px;">
                                </td>
                                <td valign="middle" style="padding-right:12px;">
                                    <div style="font-size:19px;font-weight:bold;color:#0d1830;letter-spacing:.5px;">وريد</div>
                                    <div style="font-size:11.5px;color:#8493b5;margin-top:1px;">منصتك التقنية المتكاملة</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- المحتوى --}}
                <tr>
                    <td style="padding:26px;background-color:#ffffff;">
                        @yield('content')
                    </td>
                </tr>

                {{-- تذييل بيانات الشركة --}}
                <tr>
                    <td style="padding:20px 26px 22px;background-color:#f7f9fd;border-top:1px solid #eef2fa;">
                        <div style="font-size:13px;font-weight:bold;color:#0d1830;margin-bottom:8px;">
                            {{ $mailLegalName }}
                        </div>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="font-size:12px;color:#55638a;">
                            @if ($mailAddress)
                                <tr>
                                    <td style="padding:1px 0;color:#8493b5;width:74px;">العنوان</td>
                                    <td style="padding:1px 0;">{{ $mailAddress }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td style="padding:1px 0;color:#8493b5;">الهاتف</td>
                                <td style="padding:1px 0;">
                                    <a href="tel:{{ preg_replace('/\D+/', '', $mailPhone) }}"
                                       style="color:#2563eb;text-decoration:none;font-weight:bold;"
                                       dir="ltr">{{ $mailPhone }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:1px 0;color:#8493b5;">البريد</td>
                                <td style="padding:1px 0;">
                                    <a href="mailto:{{ $mailEmail }}"
                                       style="color:#2563eb;text-decoration:none;" dir="ltr">{{ $mailEmail }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:1px 0;color:#8493b5;">الموقع</td>
                                <td style="padding:1px 0;">
                                    <a href="https://wareed.vip" style="color:#2563eb;text-decoration:none;"
                                       dir="ltr">wareed.vip</a>
                                </td>
                            </tr>
                            @if ($mailTax || $mailRegister)
                                <tr>
                                    <td style="padding:1px 0;color:#8493b5;">السجلات</td>
                                    <td style="padding:1px 0;">
                                        @if ($mailTax)<span>الرقم الضريبي: <span dir="ltr">{{ $mailTax }}</span></span>@endif
                                        @if ($mailTax && $mailRegister)<span style="color:#c8d2e6;"> · </span>@endif
                                        @if ($mailRegister)<span>س.ت: <span dir="ltr">{{ $mailRegister }}</span></span>@endif
                                    </td>
                                </tr>
                            @endif
                        </table>

                        <div style="border-top:1px solid #e6ecf7;margin-top:12px;padding-top:10px;
                                    font-size:11px;color:#9aa8c6;">
                            © {{ date('Y') }} {{ $mailLegalName }} — جميع الحقوق محفوظة.
                        </div>
                    </td>
                </tr>
            </table>

            <div style="max-width:600px;margin:14px auto 0;font-size:11px;color:#9aa8c6;text-align:center;">
                وصلتك هذه الرسالة لأنك تتعامل مع وريد بخصوص مشروعك.
            </div>

        </td>
    </tr>
</table>

</body>
</html>
