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

        /* محرّر عرض السعر */
        .wq-editor { border-top: 2px solid rgb(37 99 235); background: rgb(248 250 255); padding: 1rem; }
        .wq-editor h3 { font-size: .95rem; font-weight: 700; margin-bottom: .15rem; }
        .wq-editor .sub { font-size: .78rem; color: rgb(107 114 128); margin-bottom: .9rem; }
        .wq-items { display: flex; flex-direction: column; gap: .5rem; }
        .wq-item { display: grid; grid-template-columns: 1fr 1fr 5rem 8rem 2rem; gap: .5rem; align-items: start; }
        .wq-item .idx { display: none; }
        .wq-lbl { display: block; font-size: .7rem; color: rgb(107 114 128); margin-bottom: .15rem; }
        .wq-in {
            width: 100%; padding: .45rem .6rem; border-radius: .5rem; font-size: .85rem;
            border: 1px solid rgb(209 213 219); background: #fff; color: rgb(17 24 39);
        }
        .wq-in:focus { outline: 2px solid rgba(37,99,235,.35); outline-offset: 1px; border-color: rgb(37 99 235); }
        .wq-in.num { text-align: center; font-variant-numeric: tabular-nums; }
        .wq-del {
            margin-top: 1.15rem; width: 2rem; height: 2rem; border-radius: .5rem; cursor: pointer;
            border: 1px solid rgb(254 202 202); background: rgb(254 242 242); color: rgb(185 28 28); font-size: 1rem; line-height: 1;
        }
        .wq-del:hover { background: rgb(254 226 226); }
        .wq-opts { display: grid; grid-template-columns: repeat(5, 1fr); gap: .5rem; margin-top: .9rem; }
        .wq-sum { display: flex; flex-wrap: wrap; gap: 1.25rem; align-items: center; margin-top: .9rem; padding: .7rem 1rem; border-radius: .6rem; background: #fff; border: 1px solid rgb(229 231 235); }
        .wq-sum span { font-size: .8rem; color: rgb(107 114 128); }
        .wq-sum b { color: rgb(17 24 39); font-variant-numeric: tabular-nums; }
        .wq-sum .grand { margin-inline-start: auto; font-size: 1.05rem; font-weight: 700; color: rgb(37 99 235); }
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

        @media (max-width: 900px) {
            .wq-item { grid-template-columns: 1fr 1fr; }
            .wq-opts { grid-template-columns: repeat(2, 1fr); }
            .wq-del { margin-top: 0; }
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
                            البنود مقترحة من الخدمات التي اختارها العميل. عدّلها وأضف الأسعار،
                            وعند الإصدار يظهر العرض للعميل في صفحة طلبه ويصله بريد من {{ setting('contact_email', 'info@wareed.vip') }}.
                        </p>

                        <div class="wq-items">
                            @foreach ($draft['items'] ?? [] as $i => $item)
                                <div class="wq-item" wire:key="item-{{ $r['id'] }}-{{ $i }}">
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
                                    <div>
                                        @if ($i === 0)<label class="wq-lbl">الكمية</label>@endif
                                        <input class="wq-in num" type="number" min="1" step="1"
                                               wire:model.live.debounce.400ms="draft.items.{{ $i }}.qty">
                                    </div>
                                    <div>
                                        @if ($i === 0)<label class="wq-lbl">سعر الوحدة</label>@endif
                                        <input class="wq-in num" type="number" min="0" step="any"
                                               wire:model.live.debounce.400ms="draft.items.{{ $i }}.price">
                                    </div>
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
                                <label class="wq-lbl">خصم</label>
                                <input class="wq-in num" type="number" min="0" step="any" wire:model.live.debounce.400ms="draft.discount">
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
                            <div>
                                <label class="wq-lbl">مدة التنفيذ</label>
                                <input class="wq-in" type="text" placeholder="مثال: 3 أسابيع" wire:model.live.debounce.400ms="draft.timeline">
                            </div>
                        </div>

                        <div style="margin-top:.9rem">
                            <label class="wq-lbl">ملاحظات تظهر في العرض (اختياري)</label>
                            <input class="wq-in" type="text" placeholder="أي شرط أو ملاحظة تريد إظهارها للعميل"
                                   wire:model.live.debounce.400ms="draft.notes">
                        </div>

                        <div class="wq-sum">
                            <span>الإجمالي قبل الخصم: <b>{{ number_format($t['subtotal'], 2) }}</b></span>
                            <span>الخصم: <b>{{ number_format($t['discount'], 2) }}</b></span>
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
