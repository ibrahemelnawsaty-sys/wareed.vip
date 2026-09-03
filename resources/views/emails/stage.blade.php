@extends('emails.layout', ['title' => $title])

@section('content')
    {!! \App\Support\MailTemplates::html($bodyText) !!}

    @if ($link)
        <p style="margin:22px 0 6px;text-align:center;">
            <a href="{{ $link }}"
               style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:bold;font-size:15px;padding:13px 30px;border-radius:10px;">
                {{ $linkLabel }}
            </a>
        </p>
    @endif

    <p style="margin:18px 0 0;color:#8493b5;font-size:12px;">
        لأي استفسار يسعدنا تواصلكم معنا على {{ setting('contact_email', 'info@wareed.vip') }}
        أو هاتفياً على {{ setting('contact_phone', '+201055789056') }}.
    </p>
@endsection
