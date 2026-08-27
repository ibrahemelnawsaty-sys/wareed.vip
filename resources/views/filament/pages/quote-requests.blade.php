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
                        <x-filament::badge color="danger">تجاوز المهلة</x-filament::badge>
                    @endif

                    <span class="wq-time">
                        استُلم: {{ $r['created']?->format('Y/m/d — H:i') }}
                        @if ($r['deadline']) · موعد العرض: {{ $r['deadline']->format('Y/m/d — H:i') }} @endif
                    </span>
                </div>

                <div class="wq-grid">
                    @foreach ([
                        ['العميل', $r['name'], false],
                        ['الجوال', $r['phone'], true],
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
