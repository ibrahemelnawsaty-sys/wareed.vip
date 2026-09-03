@extends('emails.layout', ['title' => $title])

@section('content')
    <h1 style="margin:0 0 16px;font-size:19px;font-weight:bold;color:#0d1830;line-height:1.6;">
        {{ $title }}
    </h1>

    {!! \App\Support\MailTemplates::html($bodyText) !!}

    @if ($summaryOf ?? null)
        @include('emails._quote-summary')
    @endif

    @if ($link)
        {{-- زر بجدول ليُرسَم في Outlook أيضاً --}}
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:22px 0 6px;">
            <tr>
                <td align="center" bgcolor="#2563eb" style="border-radius:10px;">
                    <a href="{{ $link }}"
                       style="display:inline-block;padding:13px 32px;font-size:15px;font-weight:bold;
                              color:#ffffff;text-decoration:none;border-radius:10px;">
                        {{ $linkLabel }}
                    </a>
                </td>
            </tr>
        </table>
    @endif
@endsection
