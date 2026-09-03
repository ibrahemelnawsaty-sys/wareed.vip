{{--
    محرّر قوالب البريد الإلكتروني: قائمة المراحل على اليمين، والقالب ومعاينته على اليسار.
    التنسيقات محلية لأن أصناف Tailwind من هذا الملف ليست ضمن CSS المُجمَّع لـ Filament.
--}}
<x-filament-panels::page>
    <style>
        .mt-wrap { display: grid; grid-template-columns: 17rem 1fr; gap: 1rem; align-items: start; }
        .mt-card { background: #fff; border: 1px solid rgb(229 231 235); border-radius: .75rem; overflow: hidden; }
        .mt-card-head { padding: .7rem 1rem; border-bottom: 1px solid rgb(243 244 246); background: rgb(249 250 251); }
        .mt-card-head b { font-size: .88rem; }
        .mt-card-body { padding: 1rem; }

        .mt-stage {
            display: block; width: 100%; text-align: start; cursor: pointer;
            padding: .6rem .85rem; border: 0; border-bottom: 1px solid rgb(243 244 246); background: transparent;
        }
        .mt-stage:hover { background: rgb(249 250 251); }
        .mt-stage .n { display: flex; align-items: center; gap: .4rem; font-size: .85rem; font-weight: 600; color: rgb(17 24 39); }
        .mt-stage .h { font-size: .74rem; color: rgb(107 114 128); margin-top: .12rem; line-height: 1.5; }
        .mt-stage.on { background: rgb(239 246 255); box-shadow: inset 3px 0 0 rgb(37 99 235); }
        .mt-stage.on .n { color: rgb(29 78 216); }
        .mt-step { flex: 0 0 1.35rem; height: 1.35rem; border-radius: 50%; display: inline-flex; align-items: center;
                   justify-content: center; font-size: .7rem; background: rgb(243 244 246); color: rgb(107 114 128); }
        .mt-stage.on .mt-step { background: rgb(37 99 235); color: #fff; }
        .mt-edited { font-size: .66rem; font-weight: 700; color: rgb(180 83 9); background: rgb(254 243 199);
                     border-radius: 99px; padding: .05rem .4rem; }

        .mt-lbl { display: block; font-size: .74rem; color: rgb(107 114 128); margin-bottom: .25rem; }
        .mt-in {
            width: 100%; padding: .5rem .7rem; border-radius: .5rem; font-size: .88rem;
            border: 1px solid rgb(209 213 219); background: #fff; color: rgb(17 24 39);
            font-family: inherit; line-height: 1.9;
        }
        .mt-in:focus { outline: 2px solid rgba(37,99,235,.35); outline-offset: 1px; border-color: rgb(37 99 235); }
        textarea.mt-in { min-height: 17rem; resize: vertical; }
        .mt-row { margin-bottom: .8rem; }

        .mt-vars { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .35rem; }
        .mt-var {
            cursor: pointer; font-size: .72rem; font-weight: 600; padding: .2rem .5rem; border-radius: 99px;
            border: 1px solid rgb(191 219 254); background: rgb(239 246 255); color: rgb(29 78 216);
        }
        .mt-var:hover { background: rgb(219 234 254); }

        .mt-actions { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .9rem; }
        .mt-actions .end { margin-inline-start: auto; display: flex; flex-wrap: wrap; gap: .5rem; }

        /* معاينة مطابقة للرسالة الحقيقية — وضع فاتح دائماً مهما كان مظهر اللوحة */
        .mt-mail { background: #eef2f9; border-radius: .6rem; padding: 1.1rem; color-scheme: light; }
        .mt-mail-subject { font-size: .8rem; color: #55638a; margin-bottom: .7rem; }
        .mt-mail-subject b { color: #0d1830; font-size: .9rem; }
        .mt-mail-sheet { max-width: 37.5rem; margin: 0 auto; background: #fff;
                         border: 1px solid #e3eaf6; border-radius: 1rem; overflow: hidden; }
        .mt-mail-bar { height: 5px; background: linear-gradient(90deg,#3b82f6,#8b5cf6 50%,#2dd4bf); }
        .mt-mail-head { display: flex; align-items: center; gap: .75rem;
                        padding: 1.15rem 1.35rem 1rem; border-bottom: 1px solid #eef2fa; }
        .mt-mail-head img { width: 44px; height: 44px; border-radius: 11px; display: block; }
        .mt-mail-head .brand { font-size: 1.1rem; font-weight: 700; color: #0d1830; letter-spacing: .5px; }
        .mt-mail-head .sub { font-size: .7rem; color: #8493b5; margin-top: .05rem; }
        .mt-mail-body { padding: 1.35rem; font-size: .87rem; background: #fff; }
        .mt-mail-body h1 { font-size: 1.05rem; font-weight: 700; color: #0d1830; margin: 0 0 .8rem; line-height: 1.6; }
        .mt-mail-body p { margin: 0 0 .8rem; color: #55638a; line-height: 1.95; }
        .mt-cta { display: inline-block; background: #2563eb; color: #fff; text-decoration: none;
                  font-weight: 700; font-size: .85rem; padding: .68rem 1.6rem; border-radius: .6rem; }
        .mt-mail-foot { background: #f7f9fd; border-top: 1px solid #eef2fa; padding: 1.05rem 1.35rem 1.15rem; }
        .mt-mail-foot .co { font-size: .82rem; font-weight: 700; color: #0d1830; margin-bottom: .45rem; }
        .mt-mail-foot table { width: 100%; font-size: .74rem; color: #55638a; border-collapse: collapse; }
        .mt-mail-foot td { padding: .06rem 0; vertical-align: top; }
        .mt-mail-foot td:first-child { color: #8493b5; width: 6.5rem; }
        .mt-mail-foot a { color: #2563eb; text-decoration: none; }
        .mt-mail-foot .copy { border-top: 1px solid #e6ecf7; margin-top: .7rem; padding-top: .6rem;
                              font-size: .68rem; color: #9aa8c6; }

        .mt-note { font-size: .78rem; color: rgb(107 114 128); }
        .mt-to { font-size: .78rem; color: rgb(21 128 61); font-weight: 600; }
        .mt-to.none { color: rgb(180 83 9); }

        .dark .mt-card { background: rgb(17 24 39); border-color: rgba(255,255,255,.1); }
        .dark .mt-card-head { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.08); }
        .dark .mt-stage { border-color: rgba(255,255,255,.06); }
        .dark .mt-stage:hover { background: rgba(255,255,255,.04); }
        .dark .mt-stage .n { color: #fff; }
        .dark .mt-stage.on { background: rgba(37,99,235,.16); }
        .dark .mt-stage.on .n { color: rgb(191 219 254); }
        .dark .mt-step { background: rgba(255,255,255,.08); color: rgb(209 213 219); }
        .dark .mt-in { background: rgb(17 24 39); border-color: rgba(255,255,255,.15); color: #fff; }
        .dark .mt-var { background: rgba(37,99,235,.18); border-color: rgba(147,197,253,.35); color: rgb(191 219 254); }

        @media (max-width: 1100px) { .mt-wrap { grid-template-columns: 1fr; } }
    </style>

    @php $preview = $this->preview; @endphp

    <div class="mt-wrap">
        {{-- مراحل الطلب --}}
        <div class="mt-card">
            <div class="mt-card-head"><b>مراحل الطلب</b></div>
            @foreach ($this->stages as $i => $s)
                <button type="button" wire:click="select('{{ $s['key'] }}')"
                        @class(['mt-stage', 'on' => $stage === $s['key']])>
                    <span class="n">
                        <span class="mt-step">{{ $i + 1 }}</span>
                        {{ $s['label'] }}
                        @if ($s['customised'])<span class="mt-edited">معدَّل</span>@endif
                    </span>
                    <span class="h">{{ $s['hint'] }}</span>
                </button>
            @endforeach
        </div>

        <div>
            {{-- تحرير القالب --}}
            <div class="mt-card" style="margin-bottom:1rem"
                 x-data="{
                     insert(v) {
                         const ta = $refs.body;
                         const start = ta.selectionStart, end = ta.selectionEnd;
                         ta.value = ta.value.slice(0, start) + v + ta.value.slice(end);
                         ta.dispatchEvent(new Event('input'));
                         ta.focus();
                         ta.selectionStart = ta.selectionEnd = start + v.length;
                     },
                 }">
                <div class="mt-card-head">
                    <b>{{ \App\Support\MailTemplates::TEMPLATES[$stage]['label'] }}</b>
                    <span class="mt-note">— {{ \App\Support\MailTemplates::TEMPLATES[$stage]['hint'] }}</span>
                </div>
                <div class="mt-card-body">
                    <div class="mt-row">
                        <label class="mt-lbl">عنوان الرسالة</label>
                        <input class="mt-in" type="text" wire:model.live.debounce.500ms="subject">
                    </div>

                    <div class="mt-row">
                        <label class="mt-lbl">نص الرسالة — سطر فارغ يفصل بين الفقرات</label>
                        <textarea class="mt-in" x-ref="body" wire:model.live.debounce.500ms="body"></textarea>
                        <div class="mt-vars">
                            <span class="mt-note" style="align-self:center">أدرج متغيّراً:</span>
                            @foreach (\App\Support\MailTemplates::VARIABLES as $token => $desc)
                                <button type="button" class="mt-var" title="{{ $desc }}"
                                        x-on:click="insert(@js($token))">{{ $token }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-row">
                        <label class="mt-lbl">المعاينة والإرسال على طلب</label>
                        <select class="mt-in" wire:model.live="requestId">
                            <option value="">— بيانات تجريبية —</option>
                            @foreach ($this->requests as $r)
                                <option value="{{ $r['id'] }}">{{ $r['label'] }}</option>
                            @endforeach
                        </select>
                        @if ($this->requestId)
                            <p class="mt-row" style="margin:.35rem 0 0">
                                @if ($preview['to'])
                                    <span class="mt-to">سيصل البريد الإلكتروني إلى: {{ $preview['to'] }}</span>
                                @else
                                    <span class="mt-to none">هذا الطلب بلا بريد إلكتروني — الإرسال للعميل غير متاح.</span>
                                @endif
                            </p>
                        @endif
                    </div>

                    <div class="mt-actions">
                        <x-filament::button wire:click="save" size="sm" color="primary"
                                            icon="heroicon-o-check-circle">
                            حفظ القالب
                        </x-filament::button>
                        <x-filament::button wire:click="resetTemplate" size="sm" color="gray" outlined
                                            icon="heroicon-o-arrow-path"
                                            wire:confirm="سيُستبدل النص الحالي بالنص المقترح. متابعة؟">
                            استعادة النص المقترح
                        </x-filament::button>

                        <div class="end">
                            <x-filament::button wire:click="sendTest" size="sm" color="gray" outlined
                                                icon="heroicon-o-beaker">
                                إرسال تجريبي
                            </x-filament::button>
                            <x-filament::button wire:click="sendToClient" size="sm" color="primary"
                                                icon="heroicon-o-paper-airplane"
                                                :disabled="! $preview['to']"
                                                wire:confirm="سيُرسل البريد الإلكتروني إلى العميل مباشرة. متابعة؟">
                                إرسال للعميل
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- المعاينة --}}
            <div class="mt-card">
                <div class="mt-card-head"><b>معاينة الرسالة كما تصل للعميل</b></div>
                <div class="mt-card-body">
                    @php
                        $coPhone = setting('contact_phone', '01055789056');
                        $coEmail = setting('contact_email', 'info@wareed.vip');
                        $coName = setting('legal_name', 'شركة وريد لتقنية المعلومات');
                        $coAddress = setting('legal_address', setting('contact_address'));
                        $coTax = setting('tax_number');
                        $coRegister = setting('commercial_register');
                    @endphp
                    <div class="mt-mail">
                        <p class="mt-mail-subject">الموضوع: <b>{{ $preview['subject'] }}</b></p>

                        <div class="mt-mail-sheet">
                            <div class="mt-mail-bar"></div>

                            <div class="mt-mail-head">
                                <img src="{{ url('/images/wareed-mark.png') }}" alt="وريد">
                                <div>
                                    <div class="brand">وريد</div>
                                    <div class="sub">منصتك التقنية المتكاملة</div>
                                </div>
                            </div>

                            <div class="mt-mail-body">
                                <h1>{{ $preview['subject'] }}</h1>
                                {!! $preview['html'] !!}
                                <p style="margin:1.3rem 0 .2rem">
                                    <a class="mt-cta" href="{{ $preview['link'] }}" target="_blank" rel="noopener">متابعة الطلب</a>
                                </p>
                            </div>

                            <div class="mt-mail-foot">
                                <div class="co">{{ $coName }}</div>
                                <table>
                                    @if ($coAddress)
                                        <tr><td>العنوان</td><td>{{ $coAddress }}</td></tr>
                                    @endif
                                    <tr><td>الهاتف</td><td><a href="#" dir="ltr" onclick="return false"><b>{{ $coPhone }}</b></a></td></tr>
                                    <tr><td>البريد الإلكتروني</td><td><a href="#" dir="ltr" onclick="return false">{{ $coEmail }}</a></td></tr>
                                    <tr><td>الموقع</td><td><a href="#" dir="ltr" onclick="return false">wareed.vip</a></td></tr>
                                    @if ($coTax || $coRegister)
                                        <tr>
                                            <td>السجلات</td>
                                            <td>
                                                @if ($coTax)الرقم الضريبي: <span dir="ltr">{{ $coTax }}</span>@endif
                                                @if ($coTax && $coRegister) · @endif
                                                @if ($coRegister)س.ت: <span dir="ltr">{{ $coRegister }}</span>@endif
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                                <div class="copy">© {{ date('Y') }} {{ $coName }} — جميع الحقوق محفوظة.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
