<x-filament-panels::page>
    @php
        $stats = $this->stats;
        $requests = $this->requests;
        $statusLabels = [
            'new' => 'جديد', 'contacted' => 'تم التواصل', 'qualified' => 'مؤهّل',
            'proposal' => 'أُرسل عرض السعر', 'won' => 'تم الكسب', 'lost' => 'مفقود',
        ];
        $statusTone = [
            'new' => 'info', 'contacted' => 'warning', 'qualified' => 'warning',
            'proposal' => 'success', 'won' => 'success', 'lost' => 'danger',
        ];
        $filters = ['all' => 'كل الطلبات', 'new' => 'الجديدة', 'invite' => 'الروابط المخصّصة'];
    @endphp

    {{-- أنماط الصفحة مضمّنة: لا تعتمد على بناء Tailwind الخاص بلوحة التحكم --}}
    <style>
        .wq-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
        .wq-stat { border: 1px solid rgb(229 231 235); border-radius: .75rem; padding: 1rem 1.15rem; background: #fff; }
        .wq-stat-l { font-size: .8rem; color: rgb(107 114 128); }
        .wq-stat-v { font-size: 1.85rem; font-weight: 700; line-height: 1.2; margin-top: .15rem; }
        .wq-stat.is-alert { border-color: rgb(252 165 165); }
        .wq-stat.is-alert .wq-stat-v { color: rgb(220 38 38); }

        .wq-bar { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-top: 1.5rem; }
        .wq-hint { margin-inline-start: auto; font-size: .8rem; color: rgb(107 114 128); }

        .wq-list { display: flex; flex-direction: column; gap: 1rem; margin-top: 1.25rem; }
        .wq-card { border: 1px solid rgb(229 231 235); border-radius: .75rem; background: #fff; overflow: hidden; }
        .wq-card.is-overdue { border-color: rgb(252 165 165); }

        .wq-head { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; padding: .7rem 1rem; background: rgb(249 250 251); border-bottom: 1px solid rgb(243 244 246); }
        .wq-ref { font-family: ui-monospace, "SFMono-Regular", Menlo, monospace; font-size: .88rem; font-weight: 700; letter-spacing: .04em; color: rgb(37 99 235); direction: ltr; }
        .wq-time { margin-inline-start: auto; font-size: .75rem; color: rgb(107 114 128); }

        .wq-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .35rem 1.5rem; padding: .8rem 1rem; }
        .wq-grid.wq-2 { grid-template-columns: repeat(2, 1fr); border-top: 1px solid rgb(243 244 246); }
        .wq-k { font-size: .72rem; color: rgb(107 114 128); }
        .wq-v { font-size: .86rem; font-weight: 600; overflow-wrap: anywhere; }
        .wq-pair { font-size: .85rem; display: flex; gap: .4rem; }
        .wq-pair > span:first-child { color: rgb(107 114 128); flex: 0 0 auto; }
        .wq-pair > span:last-child { font-weight: 600; overflow-wrap: anywhere; }

        .wq-notes { padding: .8rem 1rem; border-top: 1px solid rgb(243 244 246); }
        .wq-notes p { font-size: .86rem; margin-top: .2rem; }

        .wq-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; padding: .7rem 1rem; background: rgb(249 250 251); border-top: 1px solid rgb(243 244 246); }
        .wq-actions .wq-right { margin-inline-start: auto; display: flex; flex-wrap: wrap; gap: .5rem; }

        .wq-empty { border: 1px dashed rgb(209 213 219); border-radius: .75rem; padding: 2.5rem 1rem; text-align: center; color: rgb(107 114 128); margin-top: 1.25rem; }

        /* شريط مراحل الطلب */
        .wq-flow { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; padding: .75rem 1rem; border-top: 1px solid rgb(243 244 246); background: rgb(250 251 255); }
        .wq-flow-steps { display: flex; flex-wrap: wrap; align-items: center; gap: .3rem; }
        .wq-pip { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 600; color: rgb(156 163 175); white-space: nowrap; }
        .wq-pip i { width: .5rem; height: .5rem; border-radius: 50%; background: rgb(209 213 219); display: inline-block; }
        .wq-pip.done { color: rgb(21 128 61); } .wq-pip.done i { background: rgb(34 197 94); }
        .wq-pip.now { color: rgb(37 99 235); font-weight: 700; } .wq-pip.now i { background: rgb(37 99 235); box-shadow: 0 0 0 3px rgba(37,99,235,.18); }
        .wq-pip-sep { color: rgb(209 213 219); font-size: .7rem; }
        .wq-flow-do { margin-inline-start: auto; display: flex; flex-wrap: wrap; align-items: center; gap: .45rem; }
        .wq-when { font-size: .74rem; color: rgb(107 114 128); }
        .wq-when b { color: rgb(17 24 39); }
        .wq-dt { padding: .35rem .5rem; border-radius: .45rem; border: 1px solid rgb(209 213 219); background: #fff; font-size: .78rem; color: rgb(17 24 39); }
        .dark .wq-flow { background: rgba(255,255,255,.03); border-color: rgba(255,255,255,.08); }
        .dark .wq-dt { background: rgb(17 24 39); border-color: rgba(255,255,255,.15); color: #fff; }
        .dark .wq-when b { color: #fff; }

        /* محرّر عرض السعر */
        .wq-editor { border-top: 2px solid rgb(37 99 235); background: rgb(248 250 255); padding: 1rem; }
        .wq-editor h3 { font-size: .95rem; font-weight: 700; margin-bottom: .15rem; }
        .wq-editor .sub { font-size: .78rem; color: rgb(107 114 128); margin-bottom: .9rem; }
        .wq-items { display: flex; flex-direction: column; gap: .5rem; }
        .wq-item { display: grid; grid-template-columns: 1.75rem 8rem 1fr 1fr 8rem 8.5rem 7rem 4rem 2rem; gap: .5rem; align-items: start; border-radius: .55rem; border-block: 2px solid transparent; }
        .wq-qty { display: grid; grid-template-columns: 3.4rem 1fr; gap: .3rem; }
        .wq-free { display: inline-flex; align-items: center; gap: .3rem; font-size: .75rem; font-weight: 600; color: rgb(21 128 61); cursor: pointer; user-select: none; }

        /* إعادة ترتيب البنود: مقبض سحب على الشاشات الكبيرة وأزرار سهمين على الجوال */
        .wq-move { display: flex; align-items: center; gap: .3rem; }
        .wq-grip {
            width: 1.75rem; height: 2rem; border-radius: .5rem; cursor: grab; touch-action: none;
            border: 1px solid rgb(229 231 235); background: rgb(249 250 251); color: rgb(107 114 128);
            display: flex; align-items: center; justify-content: center; line-height: 1; font-size: .95rem;
        }
        .wq-grip:hover { background: rgb(239 246 255); border-color: rgb(191 219 254); color: rgb(37 99 235); }
        .wq-grip:focus-visible { outline: 2px solid rgba(37,99,235,.45); outline-offset: 1px; }
        .wq-grip:active { cursor: grabbing; }
        .wq-move-n, .wq-move-btns { display: none; }
        .wq-nudge {
            width: 1.9rem; height: 1.9rem; border-radius: .45rem; cursor: pointer; line-height: 1; font-size: .9rem;
            border: 1px solid rgb(229 231 235); background: #fff; color: rgb(55 65 81);
        }
        .wq-nudge:disabled { opacity: .35; cursor: not-allowed; }
        .wq-item.is-drag { opacity: .4; }
        /* خط الإدراج: يبيّن الفراغ الذي سيستقر فيه البند — فوق الصف أو تحته */
        .wq-item.ins-before { border-top-color: rgb(37 99 235); }
        .wq-item.ins-after { border-bottom-color: rgb(37 99 235); }
        .wq-item.has-lbl .wq-move, .wq-item.has-lbl .wq-free, .wq-item.has-lbl .wq-del { margin-top: 1.15rem; }
        .dark .wq-grip, .dark .wq-nudge { background: rgb(17 24 39); border-color: rgba(255,255,255,.15); color: rgb(209 213 219); }
        .dark .wq-grip:hover { background: rgba(37,99,235,.18); border-color: rgba(147,197,253,.4); color: rgb(191 219 254); }
        .wq-free input { width: 1rem; height: 1rem; accent-color: rgb(22 163 74); cursor: pointer; }
        .wq-in:disabled { background: rgb(243 244 246); color: rgb(156 163 175); cursor: not-allowed; }
        .dark .wq-in:disabled { background: rgba(255,255,255,.06); }
        .wq-item .idx { display: none; }
        .wq-lbl { display: block; font-size: .7rem; color: rgb(107 114 128); margin-bottom: .15rem; }
        .wq-in {
            width: 100%; padding: .45rem .6rem; border-radius: .5rem; font-size: .85rem;
            border: 1px solid rgb(209 213 219); background: #fff; color: rgb(17 24 39);
        }
        .wq-in:focus { outline: 2px solid rgba(37,99,235,.35); outline-offset: 1px; border-color: rgb(37 99 235); }
        .wq-in.num { text-align: center; font-variant-numeric: tabular-nums; }
        .wq-del {
            width: 2rem; height: 2rem; border-radius: .5rem; cursor: pointer;
            border: 1px solid rgb(254 202 202); background: rgb(254 242 242); color: rgb(185 28 28); font-size: 1rem; line-height: 1;
        }
        .wq-del:hover { background: rgb(254 226 226); }
        .wq-opts { display: grid; grid-template-columns: repeat(4, 1fr); gap: .5rem; margin-top: .9rem; }
        .wq-sum { display: flex; flex-wrap: wrap; gap: 1.25rem; align-items: center; margin-top: .9rem; padding: .7rem 1rem; border-radius: .6rem; background: #fff; border: 1px solid rgb(229 231 235); }
        .wq-sum span { font-size: .8rem; color: rgb(107 114 128); }
        .wq-sum b { color: rgb(17 24 39); font-variant-numeric: tabular-nums; }
        .wq-sum .grand { margin-inline-start: auto; font-size: 1.05rem; font-weight: 700; color: rgb(37 99 235); }
        .wq-pay { margin-top: .9rem; padding: .75rem 1rem; border-radius: .6rem; background: #fff; border: 1px solid rgb(229 231 235); }
        .wq-pay-head { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; margin-bottom: .55rem; font-size: .85rem; }
        .wq-pay-note { font-size: .76rem; color: rgb(107 114 128); }
        .wq-pay-head .fi-btn { margin-inline-start: auto; }
        .wq-good { color: rgb(21 128 61); }
        .wq-bad { color: rgb(202 138 4); }
        .wq-pay-row { display: grid; grid-template-columns: 1fr 1fr 9.5rem 4.5rem 8.5rem 2rem; gap: .5rem; align-items: center; margin-bottom: .4rem; }
        .wq-date { font-variant-numeric: tabular-nums; }
        .wq-stage-row { display: grid; grid-template-columns: 1fr 11rem 2rem; gap: .5rem; align-items: center; margin-bottom: .4rem; }
        .wq-empty-hint { font-size: .78rem; color: rgb(156 163 175); padding: .35rem 0; }
        .wq-pay-amt { font-size: .85rem; font-weight: 700; color: rgb(37 99 235); font-variant-numeric: tabular-nums; text-align: center; }
        .dark .wq-pay { background: rgb(17 24 39); border-color: rgba(255,255,255,.1); }
        @media (max-width: 900px) {
            .wq-pay-row { grid-template-columns: 1fr 1fr; }
            .wq-stage-row { grid-template-columns: 1fr 2rem; }
            .wq-stage-row .wq-date { grid-column: 1 / -1; }
        }

        .wq-editor-actions { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .9rem; }
        .wq-editor-actions .end { margin-inline-start: auto; display: flex; gap: .5rem; }
        .wq-quote-badge { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; padding: .7rem 1rem; border-top: 1px solid rgb(243 244 246); background: rgb(240 253 244); }
        .wq-quote-badge .amt { font-weight: 700; color: rgb(21 128 61); font-variant-numeric: tabular-nums; }
        .wq-quote-badge .meta { font-size: .78rem; color: rgb(107 114 128); }

        .dark .wq-editor { background: rgba(37,99,235,.08); }
        .dark .wq-in { background: rgb(17 24 39); border-color: rgba(255,255,255,.15); color: #fff; }
        .dark .wq-sum { background: rgb(17 24 39); border-color: rgba(255,255,255,.1); }
        .dark .wq-sum b { color: #fff; }
        .dark .wq-quote-badge { background: rgba(34,197,94,.1); border-color: rgba(255,255,255,.08); }
        .dark .wq-del { background: rgba(239,68,68,.12); border-color: rgba(239,68,68,.3); color: rgb(252 165 165); }

        @media (max-width: 1400px) {
            .wq-item { grid-template-columns: 1.75rem 7rem 1fr 1fr 8rem 6.5rem 3.5rem 2rem; }
            .wq-item .wq-note-col { grid-column: 3 / -1; }
        }
        @media (max-width: 900px) {
            .wq-item { grid-template-columns: 1fr 1fr; padding-top: .35rem; border-top: 2px dashed rgb(229 231 235); }
            .wq-item .wq-note-col { grid-column: auto; }
            .wq-opts { grid-template-columns: repeat(2, 1fr); }
            .wq-item.has-lbl .wq-move, .wq-item.has-lbl .wq-free, .wq-item.has-lbl .wq-del { margin-top: 0; }
            /* السحب لا يعمل باللمس — نستبدله بسهمين واضحين */
            .wq-item .wq-move { grid-column: 1 / -1; justify-content: space-between; }
            .wq-grip { display: none; }
            .wq-move-n { display: inline-flex; font-size: .75rem; font-weight: 700; color: rgb(107 114 128); }
            .wq-move-btns { display: inline-flex; gap: .3rem; }
            .dark .wq-item { border-color: rgba(255,255,255,.1); }
        }

        .dark .wq-stat, .dark .wq-card { background: rgb(17 24 39); border-color: rgba(255,255,255,.1); }
        .dark .wq-head, .dark .wq-actions { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.08); }
        .dark .wq-grid.wq-2, .dark .wq-notes { border-color: rgba(255,255,255,.08); }
        .dark .wq-ref { color: rgb(147 197 253); }
        .dark .wq-empty { border-color: rgba(255,255,255,.15); }

        @media (max-width: 1024px) { .wq-stats, .wq-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .wq-stats, .wq-grid, .wq-grid.wq-2 { grid-template-columns: 1fr; } .wq-time { margin-inline-start: 0; width: 100%; } }
    </style>

    {{-- بطاقات موجزة --}}
    <div class="wq-stats">
        @foreach ([
            ['إجمالي الطلبات', $stats['total'], false],
            ['طلبات جديدة', $stats['new'], false],
            ['طلبات اليوم', $stats['today'], false],
            ['تجاوزت المهلة', $stats['overdue'], true],
        ] as [$label, $value, $alert])
            <div class="wq-stat @if($alert && $value > 0) is-alert @endif">
                <div class="wq-stat-l">{{ $label }}</div>
                <div class="wq-stat-v">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- مرشّحات --}}
    <div class="wq-bar">
        @foreach ($filters as $key => $label)
            <x-filament::button
                wire:click="setFilter('{{ $key }}')"
                :color="$filter === $key ? 'primary' : 'gray'"
                size="sm"
            >{{ $label }}</x-filament::button>
        @endforeach
        <span class="wq-hint">يُعرض حتى 100 طلب — الأحدث أولاً</span>
        <x-filament::button wire:click="sendTestEmail" size="sm" color="gray" icon="heroicon-o-envelope">
            اختبار البريد
        </x-filament::button>
    </div>

    {{-- الطلبات --}}
    <div class="wq-list">
        @forelse ($requests as $r)
            <div class="wq-card @if($r['overdue']) is-overdue @endif">
                <div class="wq-head">
                    <span class="wq-ref">{{ $r['reference'] }}</span>

                    <x-filament::badge :color="$statusTone[$r['status']] ?? 'gray'">
                        {{ $statusLabels[$r['status']] ?? $r['status'] }}
                    </x-filament::badge>

                    @if ($r['invite'])
                        <x-filament::badge color="gray">رابط مخصّص: {{ $r['invite'] }}</x-filament::badge>
                    @endif

                    @if ($r['overdue'])
                        <x-filament::badge color="danger">تجاوز مهلة {{ \App\Http\Controllers\QuoteController::SLA_BUSINESS_DAYS }} أيام عمل</x-filament::badge>
                    @endif

                    <span class="wq-time">
                        استُلم: {{ $r['created']?->format('Y/m/d — H:i') }}
                        @if ($r['deadline']) · موعد العرض: {{ $r['deadline']->format('Y/m/d — H:i') }} @endif
                    </span>
                </div>

                <div class="wq-grid">
                    @foreach ([
                        ['العميل', $r['name'], false],
                        ['الموبايل', $r['phone'], true],
                        ['البريد', $r['email'], true],
                        ['المتجر', $r['company'], false],
                    ] as [$label, $value, $ltr])
                        <div>
                            <div class="wq-k">{{ $label }}</div>
                            <div class="wq-v" @if($ltr) dir="ltr" @endif>{{ $value ?: '—' }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="wq-grid wq-2">
                    @foreach ($r['payload'] as $key => $value)
                        <div class="wq-pair">
                            <span>{{ $key }}:</span>
                            <span>{{ is_array($value) ? implode('، ', $value) : $value }}</span>
                        </div>
                    @endforeach
                    @if ($r['budget'])
                        <div class="wq-pair"><span>الميزانية:</span><span>{{ $r['budget'] }}</span></div>
                    @endif
                </div>

                @if ($r['message'])
                    <div class="wq-notes">
                        <div class="wq-k">ملاحظات العميل</div>
                        <p>{{ $r['message'] }}</p>
                    </div>
                @endif

                @php
                    $flow = $r['flow'];
                    $stages = \App\Http\Controllers\QuoteController::STAGES;
                    $keys = array_keys($stages);
                    $fdt = fn ($d) => $d?->format('Y/m/d — H:i');
                @endphp
                <div class="wq-flow">
                    <div class="wq-flow-steps">
                        @foreach ($keys as $i => $key)
                            <span @class(['wq-pip', 'done' => $i < $flow['index'], 'now' => $i === $flow['index']])>
                                <i></i>{{ $stages[$key]['label'] }}
                            </span>
                            @if (! $loop->last)<span class="wq-pip-sep">←</span>@endif
                        @endforeach
                    </div>

                    <div class="wq-flow-do">
                        @switch ($flow['stage'])
                            @case ('awaiting_meeting')
                                <input type="datetime-local" class="wq-dt" wire:model="flowInput.{{ $r['id'] }}.meeting_at">
                                <x-filament::button wire:click="setMeeting({{ $r['id'] }})" size="sm" color="primary" icon="heroicon-o-calendar-days">
                                    تثبيت موعد الاجتماع
                                </x-filament::button>
                                @break

                            @case ('meeting_scheduled')
                                <span class="wq-when">الاجتماع: <b>{{ $fdt($flow['meeting_at']) }}</b></span>
                                <x-filament::button wire:click="meetingDone({{ $r['id'] }})" size="sm" color="success" icon="heroicon-o-check">
                                    تم الاجتماع — ابدأ مهلة العرض
                                </x-filament::button>
                                <x-filament::button wire:click="resetStage({{ $r['id'] }}, 'awaiting_meeting')" size="sm" color="gray">
                                    تغيير الموعد
                                </x-filament::button>
                                @break

                            @case ('quote_due')
                                <span class="wq-when">
                                    تسليم العرض قبل: <b>{{ $fdt($flow['count_to']) }}</b>
                                    @if ($flow['count_to']?->isPast()) <span style="color:rgb(220 38 38)">(تأخّر)</span> @endif
                                </span>
                                @break

                            @case ('awaiting_approval')
                                <span class="wq-when">بانتظار اعتماد العميل</span>
                                <input type="date" class="wq-dt" wire:model="flowInput.{{ $r['id'] }}.due_at">
                                <x-filament::button wire:click="startExecution({{ $r['id'] }})" size="sm" color="success" icon="heroicon-o-play">
                                    اعتُمد — ابدأ التنفيذ
                                </x-filament::button>
                                @break

                            @case ('in_progress')
                                <span class="wq-when">
                                    التسليم قبل: <b>{{ $flow['due_at']?->format('Y/m/d') }}</b>
                                    @if ($flow['due_at']?->isPast()) <span style="color:rgb(220 38 38)">(تأخّر)</span> @endif
                                </span>
                                <x-filament::button wire:click="markDelivered({{ $r['id'] }})" size="sm" color="success" icon="heroicon-o-check-badge">
                                    تم التسليم
                                </x-filament::button>
                                @break

                            @case ('delivered')
                                <span class="wq-when">سُلّم: <b>{{ $fdt($flow['delivered_at']) }}</b></span>
                                <x-filament::button wire:click="resetStage({{ $r['id'] }}, 'in_progress')" size="sm" color="gray">
                                    إرجاع للتنفيذ
                                </x-filament::button>
                                @break
                        @endswitch
                    </div>
                </div>

                @if ($r['quote'])
                    <div class="wq-quote-badge">
                        <x-filament::badge color="success">صدر عرض السعر</x-filament::badge>
                        <span class="amt">
                            {{ number_format($r['quote']['total'], $r['quote']['total'] == (int) $r['quote']['total'] ? 0 : 2) }}
                            {{ $r['quote']['currency'] }}
                        </span>
                        <span class="meta">
                            {{ count($r['quote']['items']) }} بنود ·
                            صدر {{ $r['quote']['issued_at']->format('Y/m/d') }} ·
                            صالح حتى {{ $r['quote']['valid_until']->format('Y/m/d') }}
                        </span>
                        <div style="margin-inline-start:auto;display:flex;gap:.5rem;flex-wrap:wrap">
                            <x-filament::button tag="a" :href="$r['proposal']" target="_blank" size="sm" color="success" icon="heroicon-o-document-currency-dollar">
                                عرض السعر
                            </x-filament::button>
                            <x-filament::button wire:click="openQuote({{ $r['id'] }})" size="sm" color="gray">تعديل</x-filament::button>
                            <x-filament::button wire:click="deleteQuote({{ $r['id'] }})"
                                wire:confirm="سيُحذف عرض السعر ولن يعود ظاهراً للعميل. متأكد؟"
                                size="sm" color="danger">حذف العرض</x-filament::button>
                        </div>
                    </div>
                @endif

                @if ($editingId === $r['id'])
                    @php $t = $this->draftTotals; @endphp
                    <div class="wq-editor">
                        <h3>إصدار عرض السعر — {{ $r['reference'] }}</h3>
                        <p class="sub">
                            البنود مقترحة من الخدمات التي اختارها العميل. عدّلها وأضف الأسعار.
                            اكتب اسم <b>المرحلة</b> لتجميع بنودها معاً في العرض، وعلّم <b>مجاني</b> لأي بند يظهر بـ«مجاناً»،
                            واستخدم <b>ملاحظة / اشتراك</b> لتوضيح مثل «اشتراك سنوي»، و<b>الوحدة</b> لتظهر الكمية هكذا: «12 شهر».
                            رتّب البنود بسحب المقبض <b>⠿</b> وأفلته فوق البند الذي تريده أو تحته — الخط الأزرق
                            يبيّن مكان استقراره، والترتيب هنا هو ترتيب ظهورها في العرض.
                            وعند الإصدار يظهر العرض للعميل في صفحة طلبه ويصله بريد من {{ setting('contact_email', 'info@wareed.vip') }}.
                        </p>

                        @php
                            $phaseNames = collect($draft['items'] ?? [])->pluck('phase')
                                ->map(fn ($p) => trim((string) $p))->filter()
                                ->merge(['المرحلة الأولى', 'المرحلة الثانية', 'المرحلة الثالثة'])
                                ->unique()->values();
                        @endphp
                        <datalist id="wq-phases-{{ $r['id'] }}">
                            @foreach ($phaseNames as $ph)
                                <option value="{{ $ph }}"></option>
                            @endforeach
                        </datalist>
                        <datalist id="wq-units">
                            @foreach (['شهر', 'سنة', 'صفحة', 'منتج', 'ساعة', 'يوم', 'مستخدم', 'لغة', 'باقة', 'جلسة', 'بوابة'] as $un)
                                <option value="{{ $un }}"></option>
                            @endforeach
                        </datalist>

                        <datalist id="wq-notes">
                            @foreach (['اشتراك سنوي', 'اشتراك شهري', 'دفعة واحدة', 'تجديد سنوي', 'مجاني للسنة الأولى'] as $nt)
                                <option value="{{ $nt }}"></option>
                            @endforeach
                        </datalist>

                        @php $lastItem = count($draft['items'] ?? []) - 1; @endphp
                        <div class="wq-items"
                             x-data="{
                                 from: null,
                                 over: null,
                                 line(slot) { return this.from !== null && this.over === slot
                                     && slot !== this.from && slot !== this.from + 1 },
                             }"
                             x-on:dragleave="if (! $el.contains($event.relatedTarget)) over = null">
                            @foreach ($draft['items'] ?? [] as $i => $item)
                                @php $isFree = (bool) ($item['free'] ?? false); @endphp
                                <div @class(['wq-item', 'has-lbl' => $i === 0]) wire:key="item-{{ $r['id'] }}-{{ $i }}"
                                     x-bind:class="{
                                         'is-drag': from === {{ $i }},
                                         'ins-before': line({{ $i }}),
                                         'ins-after': {{ $i === $lastItem ? 'true' : 'false' }} && line({{ $lastItem + 1 }}),
                                     }"
                                     x-on:dragstart="if ($event.target !== $el) return; from = {{ $i }};
                                                     $event.dataTransfer.effectAllowed = 'move';
                                                     $event.dataTransfer.setData('text/plain', '{{ $i }}')"
                                     x-on:dragover="if (from === null) return; $event.preventDefault();
                                                    const box = $el.getBoundingClientRect();
                                                    over = ($event.clientY - box.top) > box.height / 2 ? {{ $i + 1 }} : {{ $i }}"
                                     x-on:drop="if (from === null) return; $event.preventDefault();
                                                if (over !== null) $wire.moveItem(from, over); from = null; over = null"
                                     x-on:dragend="$el.draggable = false; from = null; over = null">
                                    <div class="wq-move">
                                        <button type="button" class="wq-grip" aria-label="إعادة ترتيب البند {{ $i + 1 }}"
                                                title="اسحب لإعادة الترتيب — أو ركّز عليه واستخدم سهمي ↑ ↓"
                                                x-on:mousedown="$el.closest('.wq-item').draggable = true"
                                                x-on:mouseup="$el.closest('.wq-item').draggable = false"
                                                x-on:keydown.arrow-up.prevent="$wire.moveItem({{ $i }}, {{ $i - 1 }})"
                                                x-on:keydown.arrow-down.prevent="$wire.moveItem({{ $i }}, {{ $i + 2 }})">⠿</button>
                                        <span class="wq-move-n">البند {{ $i + 1 }}</span>
                                        <span class="wq-move-btns">
                                            <button type="button" class="wq-nudge" title="تحريك لأعلى" @disabled($i === 0)
                                                    wire:click="moveItem({{ $i }}, {{ $i - 1 }})">↑</button>
                                            <button type="button" class="wq-nudge" title="تحريك لأسفل" @disabled($i === $lastItem)
                                                    wire:click="moveItem({{ $i }}, {{ $i + 2 }})">↓</button>
                                        </span>
                                    </div>
                                    <div>
                                        @if ($i === 0)<label class="wq-lbl">المرحلة</label>@endif
                                        <input class="wq-in" type="text" placeholder="المرحلة الأولى"
                                               list="wq-phases-{{ $r['id'] }}"
                                               wire:model.live.debounce.400ms="draft.items.{{ $i }}.phase">
                                    </div>
                                    <div>
                                        @if ($i === 0)<label class="wq-lbl">البند</label>@endif
                                        <input class="wq-in" type="text" placeholder="اسم البند"
                                               wire:model.live.debounce.400ms="draft.items.{{ $i }}.name">
                                    </div>
                                    <div>
                                        @if ($i === 0)<label class="wq-lbl">وصف مختصر (اختياري)</label>@endif
                                        <input class="wq-in" type="text" placeholder="تفاصيل تُطمئن العميل"
                                               wire:model.live.debounce.400ms="draft.items.{{ $i }}.desc">
                                    </div>
                                    <div class="wq-note-col">
                                        @if ($i === 0)<label class="wq-lbl">ملاحظة / اشتراك</label>@endif
                                        <input class="wq-in" type="text" placeholder="اشتراك سنوي"
                                               list="wq-notes"
                                               wire:model.live.debounce.400ms="draft.items.{{ $i }}.note">
                                    </div>
                                    <div>
                                        @if ($i === 0)<label class="wq-lbl">الكمية / الوحدة</label>@endif
                                        <div class="wq-qty">
                                            <input class="wq-in num" type="number" min="1" step="1"
                                                   wire:model.live.debounce.400ms="draft.items.{{ $i }}.qty">
                                            <input class="wq-in" type="text" placeholder="وحدة" list="wq-units"
                                                   wire:model.live.debounce.400ms="draft.items.{{ $i }}.unit">
                                        </div>
                                    </div>
                                    <div>
                                        @if ($i === 0)<label class="wq-lbl">سعر الوحدة</label>@endif
                                        <input class="wq-in num" type="number" min="0" step="any" @disabled($isFree)
                                               wire:model.live.debounce.400ms="draft.items.{{ $i }}.price">
                                    </div>
                                    <label class="wq-free" title="بند مجاني — يظهر «مجاناً» في العرض">
                                        <input type="checkbox" @checked($isFree) wire:click="toggleFree({{ $i }})"> مجاني
                                    </label>
                                    <button type="button" class="wq-del" wire:click="removeItem({{ $i }})" title="حذف البند">×</button>
                                </div>
                            @endforeach
                        </div>

                        <div class="wq-editor-actions">
                            <x-filament::button wire:click="addItem" size="sm" color="gray" icon="heroicon-o-plus">
                                إضافة بند
                            </x-filament::button>
                        </div>

                        <div class="wq-opts">
                            <div>
                                <label class="wq-lbl">خصم %</label>
                                <input class="wq-in num" type="number" min="0" max="100" step="any"
                                       wire:model.live.debounce.400ms="draft.discount_percent">
                            </div>
                            <div>
                                <label class="wq-lbl">ضريبة %</label>
                                <input class="wq-in num" type="number" min="0" step="any" wire:model.live.debounce.400ms="draft.vat_percent">
                            </div>
                            <div>
                                <label class="wq-lbl">العملة</label>
                                <input class="wq-in num" type="text" wire:model.live.debounce.400ms="draft.currency">
                            </div>
                            <div>
                                <label class="wq-lbl">صلاحية العرض (يوم)</label>
                                <input class="wq-in num" type="number" min="1" step="1" wire:model.live.debounce.400ms="draft.valid_days">
                            </div>
                        </div>

                        <div style="margin-top:.9rem">
                            <label class="wq-lbl">ملاحظات تظهر في العرض (اختياري)</label>
                            <input class="wq-in" type="text" placeholder="أي شرط أو ملاحظة تريد إظهارها للعميل"
                                   wire:model.live.debounce.400ms="draft.notes">
                        </div>

                        <div class="wq-pay">
                            <div class="wq-pay-head">
                                <b>الجدول الزمني للتسليم</b>
                                <span class="wq-pay-note">
                                    مراحل التنفيذ وموعد تسليم كل مرحلة — تظهر كجدول في العرض،
                                    وآخر موعد فيها يُعرض كمدة التنفيذ.
                                </span>
                                <x-filament::button wire:click="addStage" size="xs" color="gray" icon="heroicon-o-plus">
                                    إضافة مرحلة
                                </x-filament::button>
                            </div>

                            @forelse ($draft['schedule'] ?? [] as $si => $stage)
                                <div class="wq-stage-row" wire:key="stage-{{ $r['id'] }}-{{ $si }}">
                                    <input class="wq-in" type="text" placeholder="اسم المرحلة" list="wq-phases-{{ $r['id'] }}"
                                           wire:model.live.debounce.400ms="draft.schedule.{{ $si }}.phase">
                                    <input class="wq-in wq-date" type="date" title="تاريخ التسليم"
                                           wire:model.live="draft.schedule.{{ $si }}.date">
                                    <button type="button" class="wq-del" style="margin-top:0"
                                            wire:click="removeStage({{ $si }})" title="حذف المرحلة">×</button>
                                </div>
                            @empty
                                <p class="wq-empty-hint">لا مراحل مجدولة — اضغط «إضافة مرحلة» لتحديد مواعيد التسليم.</p>
                            @endforelse
                        </div>

                        <div class="wq-pay">
                            <div class="wq-pay-head">
                                <b>الدفعات</b>
                                <span class="wq-pay-note">
                                    النسبة من الإجمالي والقيمة تُحسب تلقائياً، وتاريخ الاستحقاق اختياري
                                    @php $pp = $t['payments_percent']; @endphp
                                    @if ($pp > 0)
                                        — مجموع النسب:
                                        <b @class(['wq-bad' => abs($pp - 100) > 0.01, 'wq-good' => abs($pp - 100) <= 0.01])>
                                            {{ rtrim(rtrim(number_format($pp, 2), '0'), '.') }}%
                                        </b>
                                    @endif
                                </span>
                                <x-filament::button wire:click="addPayment" size="sm" color="gray" icon="heroicon-o-plus">
                                    إضافة دفعة
                                </x-filament::button>
                            </div>

                            @foreach ($draft['payments'] ?? [] as $pi => $pay)
                                <div class="wq-pay-row" wire:key="pay-{{ $r['id'] }}-{{ $pi }}">
                                    <input class="wq-in" type="text" placeholder="اسم الدفعة"
                                           wire:model.live.debounce.400ms="draft.payments.{{ $pi }}.label">
                                    <input class="wq-in" type="text" placeholder="ملاحظة (اختياري)"
                                           wire:model.live.debounce.400ms="draft.payments.{{ $pi }}.note">
                                    <input class="wq-in wq-date" type="date" title="تاريخ الاستحقاق"
                                           wire:model.live="draft.payments.{{ $pi }}.due">
                                    <input class="wq-in num" type="number" min="0" max="100" step="any" placeholder="%"
                                           wire:model.live.debounce.400ms="draft.payments.{{ $pi }}.percent">
                                    <span class="wq-pay-amt">
                                        {{ number_format($t['payments'][$pi]['amount'] ?? 0, 2) }} {{ $t['currency'] }}
                                    </span>
                                    <button type="button" class="wq-del" style="margin-top:0" wire:click="removePayment({{ $pi }})" title="حذف الدفعة">×</button>
                                </div>
                            @endforeach
                        </div>

                        <div class="wq-sum">
                            <span>الإجمالي قبل الخصم: <b>{{ number_format($t['subtotal'], 2) }}</b></span>
                            <span>
                                الخصم@if ($t['discount_percent'] > 0) ({{ rtrim(rtrim(number_format($t['discount_percent'], 2), '0'), '.') }}%)@endif:
                                <b>{{ number_format($t['discount'], 2) }}</b>
                            </span>
                            <span>الضريبة: <b>{{ number_format($t['vat'], 2) }}</b></span>
                            <span class="grand">الإجمالي: {{ number_format($t['total'], 2) }} {{ $t['currency'] }}</span>
                        </div>

                        <div class="wq-editor-actions">
                            <x-filament::button wire:click="issueQuote(true)" size="sm" color="success" icon="heroicon-o-paper-airplane">
                                إصدار وإرسال للعميل
                            </x-filament::button>
                            <x-filament::button wire:click="issueQuote(false)" size="sm" color="gray">
                                حفظ دون إرسال
                            </x-filament::button>
                            <div class="end">
                                <x-filament::button wire:click="closeQuote" size="sm" color="gray">إغلاق</x-filament::button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="wq-actions">
                    <x-filament::button tag="a" :href="$r['document']" target="_blank" color="gray" size="sm" icon="heroicon-o-document-arrow-down">
                        مستند الطلب
                    </x-filament::button>

                    @if ($wa = App\Http\Controllers\QuoteController::waNumber($r['phone']))
                        <x-filament::button tag="a" size="sm" color="success" icon="heroicon-o-chat-bubble-left-right"
                            :href="'https://wa.me/'.$wa.'?text='.rawurlencode('مرحباً، بخصوص طلب المتجر رقم '.$r['reference'])"
                            target="_blank">واتساب</x-filament::button>
                    @endif

                    @if ($r['email'])
                        <x-filament::button tag="a" size="sm" color="gray" icon="heroicon-o-envelope"
                            :href="'mailto:'.$r['email'].'?subject='.rawurlencode('عرض سعر متجرك الإلكتروني — '.$r['reference'])">
                            بريد
                        </x-filament::button>
                    @endif

                    @unless ($r['quote'])
                        <x-filament::button wire:click="openQuote({{ $r['id'] }})" size="sm" color="primary" icon="heroicon-o-document-currency-dollar">
                            إصدار عرض سعر
                        </x-filament::button>
                    @endunless

                    <div class="wq-right">
                        @if ($r['status'] === 'new')
                            <x-filament::button wire:click="markStatus({{ $r['id'] }}, 'contacted')" size="sm" color="warning">
                                تم التواصل
                            </x-filament::button>
                        @endif
                        @if (in_array($r['status'], ['new', 'contacted', 'qualified'], true))
                            <x-filament::button wire:click="markStatus({{ $r['id'] }}, 'proposal')" size="sm" color="info">
                                أُرسل عرض السعر
                            </x-filament::button>
                        @endif
                        @if ($r['status'] !== 'won')
                            <x-filament::button wire:click="markStatus({{ $r['id'] }}, 'won')" size="sm" color="success">
                                تم الكسب
                            </x-filament::button>
                        @endif

                        <x-filament::button
                            wire:click="deleteRequest({{ $r['id'] }})"
                            wire:confirm="سيُحذف الطلب {{ $r['reference'] }} نهائياً.{{ $r['invite'] ? ' وسيعود الرابط المخصّص متاحاً لاستقبال طلب جديد.' : '' }} هل أنت متأكد؟"
                            size="sm" color="danger" icon="heroicon-o-trash">
                            حذف
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @empty
            <div class="wq-empty">لا توجد طلبات في هذا التصنيف حتى الآن.</div>
        @endforelse
    </div>
</x-filament-panels::page>
