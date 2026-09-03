{{--
    محرّر قوالب البريد: قائمة المراحل على اليمين، والقالب ومعاينته على اليسار.
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

        /* معاينة تحاكي شكل الرسالة الواصلة للعميل */
        .mt-mail { background: rgb(244 246 251); border-radius: .6rem; padding: 1rem; }
        .mt-mail-subject { font-size: .8rem; color: rgb(107 114 128); margin-bottom: .6rem; }
        .mt-mail-subject b { color: rgb(17 24 39); font-size: .9rem; }
        .mt-mail-sheet { max-width: 34rem; margin: 0 auto; }
        .mt-mail-head { background: linear-gradient(120deg,#3b82f6,#8b5cf6 50%,#2dd4bf); border-radius: .8rem .8rem 0 0;
                        padding: 1.1rem; text-align: center; color: #fff; }
        .mt-mail-head .brand { font-size: 1.15rem; font-weight: 700; letter-spacing: 1px; }
        .mt-mail-head .sub { font-size: .7rem; color: #eaf0fb; margin-top: .15rem; }
        .mt-mail-body { background: #fff; border: 1px solid rgb(230 236 247); border-top: 0;
                        border-radius: 0 0 .8rem .8rem; padding: 1.3rem 1.2rem; font-size: .85rem; }
        .mt-mail-body p { margin: 0 0 .8rem; color: rgb(85 99 138); line-height: 1.95; }
        .mt-cta { display: inline-block; background: rgb(37 99 235); color: #fff; text-decoration: none;
                  font-weight: 700; font-size: .85rem; padding: .6rem 1.4rem; border-radius: .55rem; }
        .mt-mail-foot { color: rgb(132 147 181); font-size: .72rem; margin-top: .9rem; }

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
        .dark .mt-mail { background: rgba(255,255,255,.04); }
        .dark .mt-mail-subject b { color: #fff; }
        .dark .mt-mail-body { background: rgb(15 23 42); border-color: rgba(255,255,255,.1); }
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
                                    <span class="mt-to">سيصل البريد إلى: {{ $preview['to'] }}</span>
                                @else
                                    <span class="mt-to none">هذا الطلب بلا بريد إلكتروني — الإرسال للعميل غير متاح.</span>
                                @endif
                            </p>
                        @endif
                    </div>

                    <div class="mt-actions">
                        <x-filament::button wire:click="save" size="sm" color="primary" icon="heroicon-o-check">
                            حفظ القالب
                        </x-filament::button>
                        <x-filament::button wire:click="resetTemplate" size="sm" color="gray"
                                            icon="heroicon-o-arrow-path"
                                            wire:confirm="سيُستبدل النص الحالي بالنص المقترح. متابعة؟">
                            استعادة النص المقترح
                        </x-filament::button>

                        <div class="end">
                            <x-filament::button wire:click="sendTest" size="sm" color="warning"
                                                icon="heroicon-o-beaker">
                                إرسال تجريبي
                            </x-filament::button>
                            <x-filament::button wire:click="sendToClient" size="sm" color="success"
                                                icon="heroicon-o-paper-airplane"
                                                :disabled="! $preview['to']"
                                                wire:confirm="سيُرسل البريد إلى العميل مباشرة. متابعة؟">
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
                    <div class="mt-mail">
                        <p class="mt-mail-subject">الموضوع: <b>{{ $preview['subject'] }}</b></p>
                        <div class="mt-mail-sheet">
                            <div class="mt-mail-head">
                                <div class="brand">وريد</div>
                                <div class="sub">شركة وريد لتقنية المعلومات</div>
                            </div>
                            <div class="mt-mail-body">
                                {!! $preview['html'] !!}
                                <p style="text-align:center;margin:1.2rem 0 .3rem">
                                    <a class="mt-cta" href="{{ $preview['link'] }}" target="_blank" rel="noopener">متابعة الطلب</a>
                                </p>
                                <p class="mt-mail-foot">
                                    لأي استفسار يسعدنا تواصلكم معنا على {{ setting('contact_email', 'info@wareed.vip') }}
                                    أو هاتفياً على {{ setting('contact_phone', '+201055789056') }}.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
