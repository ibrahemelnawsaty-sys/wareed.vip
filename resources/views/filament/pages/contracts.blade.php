<x-filament-panels::page>
    @php
        $stats = $this->stats;
        $rows = $this->rows;
        $signature = $this->signature;
        $filters = ['all' => 'كل العقود', 'draft' => 'مسوّدات', 'sent' => 'بانتظار العميل', 'feedback' => 'وردت ملاحظات', 'approved' => 'معتمدة'];
        $tone = ['draft' => 'gray', 'sent' => 'info', 'feedback' => 'warning', 'approved' => 'success'];
        $fdt = fn ($d) => $d?->format('Y/m/d — H:i');
    @endphp

    {{-- أنماط الصفحة مضمّنة: لا تعتمد على بناء Tailwind الخاص بلوحة التحكم --}}
    <style>
        .wc-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; }
        .wc-stat { border: 1px solid rgb(229 231 235); border-radius: .75rem; padding: 1rem 1.15rem; background: #fff; }
        .wc-stat-l { font-size: .8rem; color: rgb(107 114 128); }
        .wc-stat-v { font-size: 1.85rem; font-weight: 700; line-height: 1.2; margin-top: .15rem; }
        .wc-stat.is-alert { border-color: rgb(253 224 71); }
        .wc-stat.is-alert .wc-stat-v { color: rgb(180 83 9); }

        .wc-bar { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-top: 1.5rem; }
        .wc-sig { margin-inline-start: auto; font-size: .78rem; color: rgb(107 114 128); display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
        .wc-sig b { color: rgb(17 24 39); }
        .wc-sig a { color: rgb(37 99 235); font-weight: 600; }

        .wc-list { display: flex; flex-direction: column; gap: 1rem; margin-top: 1.25rem; }
        .wc-card { border: 1px solid rgb(229 231 235); border-radius: .75rem; background: #fff; overflow: hidden; }
        .wc-card.is-feedback { border-color: rgb(253 224 71); }
        .wc-head { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; padding: .7rem 1rem; background: rgb(249 250 251); border-bottom: 1px solid rgb(243 244 246); }
        .wc-num { font-family: ui-monospace, "SFMono-Regular", Menlo, monospace; font-size: .88rem; font-weight: 700; letter-spacing: .04em; color: rgb(37 99 235); direction: ltr; }
        .wc-ref { font-family: ui-monospace, Menlo, monospace; font-size: .74rem; color: rgb(107 114 128); direction: ltr; }
        .wc-time { margin-inline-start: auto; font-size: .75rem; color: rgb(107 114 128); }

        .wc-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .35rem 1.5rem; padding: .8rem 1rem; }
        .wc-k { font-size: .72rem; color: rgb(107 114 128); }
        .wc-v { font-size: .86rem; font-weight: 600; overflow-wrap: anywhere; }

        .wc-fb { padding: .7rem 1rem; border-top: 1px solid rgb(243 244 246); background: rgb(255 251 235); }
        .wc-fb-t { font-size: .78rem; font-weight: 700; color: rgb(180 83 9); margin-bottom: .35rem; }
        .wc-fb-row { font-size: .82rem; padding: .25rem 0; display: flex; gap: .5rem; align-items: baseline; }
        .wc-fb-row .lbl { flex: 0 0 auto; font-size: .7rem; font-weight: 700; padding: .05rem .5rem; border-radius: 99px; }
        .wc-fb-row .lbl.edit { color: rgb(180 83 9); background: rgb(254 243 199); }
        .wc-fb-row .lbl.del { color: rgb(185 28 28); background: rgb(254 226 226); }
        .wc-fb-row b { font-weight: 600; }
        .wc-fb-row .note { color: rgb(75 85 99); }

        .wc-rounds { padding: .5rem 1rem .7rem; border-top: 1px solid rgb(243 244 246); font-size: .74rem; color: rgb(107 114 128); display: flex; flex-wrap: wrap; gap: .35rem 1rem; }
        .wc-rounds b { color: rgb(17 24 39); }

        .wc-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; padding: .7rem 1rem; background: rgb(249 250 251); border-top: 1px solid rgb(243 244 246); }
        .wc-actions .wc-right { margin-inline-start: auto; display: flex; flex-wrap: wrap; gap: .5rem; }
        .wc-empty { border: 1px dashed rgb(209 213 219); border-radius: .75rem; padding: 2.5rem 1rem; text-align: center; color: rgb(107 114 128); margin-top: 1.25rem; }

        /* محرّر البنود */
        .wc-editor { border-top: 2px solid rgb(37 99 235); background: rgb(248 250 255); padding: 1rem; }
        .wc-editor h3 { font-size: .95rem; font-weight: 700; margin-bottom: .15rem; }
        .wc-editor .sub { font-size: .78rem; color: rgb(107 114 128); margin-bottom: .9rem; }
        .wc-clauses { display: flex; flex-direction: column; gap: .7rem; }
        .wc-clause { border: 1px solid rgb(229 231 235); border-radius: .65rem; background: #fff; padding: .7rem .8rem; }
        .wc-clause.is-locked { border-color: rgb(167 243 208); }
        .wc-clause.is-edit { border-color: rgb(253 224 71); }
        .wc-clause.is-del { border-color: rgb(252 165 165); }
        .wc-clause-top { display: grid; grid-template-columns: 2rem 9rem 1fr auto; gap: .5rem; align-items: center; }
        .wc-clause-no { width: 1.8rem; height: 1.8rem; border-radius: 99px; background: rgb(17 24 39); color: #fff; font-size: .75rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
        .wc-clause-state { display: flex; align-items: center; gap: .35rem; }
        .wc-in {
            width: 100%; padding: .45rem .6rem; border-radius: .5rem; font-size: .85rem;
            border: 1px solid rgb(209 213 219); background: #fff; color: rgb(17 24 39);
        }
        .wc-in:focus { outline: 2px solid rgba(37,99,235,.35); outline-offset: 1px; border-color: rgb(37 99 235); }
        textarea.wc-in { margin-top: .5rem; min-height: 5.5rem; line-height: 1.7; resize: vertical; }
        .wc-note { margin-top: .45rem; font-size: .78rem; color: rgb(120 53 15); background: rgb(255 251 235); border: 1px solid rgb(253 230 138); border-radius: .5rem; padding: .4rem .6rem; }
        .wc-note b { font-weight: 700; }
        .wc-tools { display: flex; gap: .3rem; align-items: center; }
        .wc-btn {
            width: 1.9rem; height: 1.9rem; border-radius: .45rem; cursor: pointer; line-height: 1; font-size: .9rem;
            border: 1px solid rgb(229 231 235); background: #fff; color: rgb(55 65 81);
        }
        .wc-btn:disabled { opacity: .35; cursor: not-allowed; }
        .wc-btn.del { border-color: rgb(254 202 202); background: rgb(254 242 242); color: rgb(185 28 28); }
        .wc-editor-actions { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; margin-top: .9rem; }
        .wc-editor-actions .wc-right { margin-inline-start: auto; display: flex; flex-wrap: wrap; gap: .5rem; }
        .dark .wc-stat, .dark .wc-card, .dark .wc-clause { background: rgb(24 24 27); border-color: rgba(255,255,255,.08); }
        .dark .wc-head, .dark .wc-actions { background: rgba(255,255,255,.03); }
        .dark .wc-editor { background: rgba(37,99,235,.06); }
        .dark .wc-in { background: rgb(17 24 39); border-color: rgba(255,255,255,.15); color: #fff; }
        .dark .wc-fb { background: rgba(245,158,11,.08); }
        .dark .wc-note { background: rgba(245,158,11,.1); color: rgb(253 230 138); border-color: rgba(245,158,11,.3); }
        @media (max-width: 900px) { .wc-stats { grid-template-columns: repeat(2, 1fr); } .wc-grid { grid-template-columns: 1fr 1fr; } .wc-clause-top { grid-template-columns: 2rem 1fr; } }
    </style>

    <div class="wc-stats">
        <div class="wc-stat"><div class="wc-stat-l">إجمالي العقود</div><div class="wc-stat-v">{{ $stats['total'] }}</div></div>
        <div class="wc-stat"><div class="wc-stat-l">مسوّدات لم تُرسل</div><div class="wc-stat-v">{{ $stats['draft'] }}</div></div>
        <div class="wc-stat"><div class="wc-stat-l">بانتظار مراجعة العميل</div><div class="wc-stat-v">{{ $stats['sent'] }}</div></div>
        <div @class(['wc-stat', 'is-alert' => $stats['feedback'] > 0])><div class="wc-stat-l">وردت ملاحظات</div><div class="wc-stat-v">{{ $stats['feedback'] }}</div></div>
        <div class="wc-stat"><div class="wc-stat-l">معتمدة</div><div class="wc-stat-v">{{ $stats['approved'] }}</div></div>
    </div>

    <div class="wc-bar">
        @foreach ($filters as $key => $label)
            <x-filament::button wire:click="setFilter('{{ $key }}')" size="sm" :color="$filter === $key ? 'primary' : 'gray'">
                {{ $label }}
            </x-filament::button>
        @endforeach
        <div class="wc-sig">
            <span>التوقيع عن الشركة: <b>{{ $signature['name'] }}</b> — {{ $signature['title'] }}</span>
            <span>· الختم: <b>{{ $signature['stamp_url'] ? 'مرفوع' : 'غير مرفوع' }}</b></span>
            <a href="{{ route('filament.admin.pages.manage-settings') }}">تعديل من إعدادات الموقع</a>
        </div>
    </div>

    @if (! count($rows))
        <div class="wc-empty">
            لا توجد عقود بعد — تُنشأ مسوّدة العقد تلقائياً بمجرد اعتماد العميل لعرض السعر، أو يدوياً من زر «إنشاء مسوّدة العقد» في صفحة متابعة الطلبات.
        </div>
    @endif

    <div class="wc-list">
        @foreach ($rows as $r)
            @php $c = $r['contract']; $status = $c['status'] ?? 'draft'; @endphp
            <div @class(['wc-card', 'is-feedback' => $status === 'feedback'])>
                <div class="wc-head">
                    <span class="wc-num">{{ $c['number'] ?? 'بلا عقد بعد' }}</span>
                    <span class="wc-ref">{{ $r['reference'] }}</span>
                    <x-filament::badge :color="$tone[$status]">{{ $c['status_label'] ?? 'لم تُنشأ المسوّدة' }}</x-filament::badge>
                    @if ($c && $c['round'] > 0)
                        <x-filament::badge color="gray">الجولة {{ $c['round'] }}</x-filament::badge>
                    @endif
                    @if ($c)
                        <x-filament::badge color="success">{{ $c['approved_count'] }} معتمد</x-filament::badge>
                        @if ($c['objections_count'] > 0)
                            <x-filament::badge color="warning">{{ $c['objections_count'] }} بملاحظات</x-filament::badge>
                        @endif
                        @if ($c['pending_count'] > 0 && $c['is_sent'] && ! $c['is_approved'])
                            <x-filament::badge color="info">{{ $c['pending_count'] }} بانتظار القرار</x-filament::badge>
                        @endif
                    @endif
                    <span class="wc-time">
                        @if ($c && $c['approved_at']) اعتُمد {{ $fdt($c['approved_at']) }}
                        @elseif ($c && $c['feedback_at']) ملاحظات {{ $fdt($c['feedback_at']) }}
                        @elseif ($c && $c['sent_at']) أُرسل {{ $fdt($c['sent_at']) }}
                        @elseif ($c && $c['created_at']) أُنشئ {{ $fdt($c['created_at']) }}
                        @endif
                    </span>
                </div>

                <div class="wc-grid">
                    <div><div class="wc-k">العميل</div><div class="wc-v">{{ $r['name'] }}</div></div>
                    <div><div class="wc-k">المتجر</div><div class="wc-v">{{ $r['company'] ?: '—' }}</div></div>
                    <div><div class="wc-k">البريد الإلكتروني</div><div class="wc-v" dir="ltr">{{ $r['email'] ?: '—' }}</div></div>
                    <div>
                        <div class="wc-k">قيمة العرض المعتمد</div>
                        <div class="wc-v">
                            @if ($r['quote'])
                                {{ number_format($r['quote']['total'], $r['quote']['total'] == (int) $r['quote']['total'] ? 0 : 2) }} {{ $r['quote']['currency'] }}
                                <span style="font-weight:400;color:rgb(107 114 128)">(الإصدار {{ $r['quote']['version'] }})</span>
                            @else
                                لم يُصدر عرض سعر
                            @endif
                        </div>
                    </div>
                </div>

                @if ($c && $c['objections_count'] > 0)
                    <div class="wc-fb">
                        <div class="wc-fb-t">ملاحظات العميل — الجولة {{ $c['round'] }}</div>
                        @foreach ($c['clauses'] as $clause)
                            @if (in_array($clause['decision'], ['edited', 'deleted'], true))
                                <div class="wc-fb-row">
                                    <span class="lbl {{ $clause['decision'] === 'edited' ? 'edit' : 'del' }}">{{ $clause['decision_label'] }}</span>
                                    <b>{{ $clause['title'] }}</b>
                                    <span class="note">— {{ $clause['note'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if ($c && count($c['rounds']))
                    <div class="wc-rounds">
                        @foreach ($c['rounds'] as $round)
                            <span>
                                الجولة <b>{{ $round['round'] }}</b>: أُرسلت {{ $fdt($round['sent_at']) }}
                                @if ($round['outcome'] === 'approved') — <b>اعتُمد العقد</b> {{ $fdt($round['decided_at']) }}
                                @elseif ($round['outcome'] === 'feedback') — وردت ملاحظات {{ $fdt($round['decided_at']) }}
                                @else — بانتظار العميل
                                @endif
                            </span>
                        @endforeach
                    </div>
                @endif

                <div class="wc-actions">
                    @if ($c && $r['review_url'])
                        <x-filament::button tag="a" :href="$r['review_url']" target="_blank" size="sm" color="gray" icon="heroicon-o-arrow-top-right-on-square">
                            رابط العميل
                        </x-filament::button>
                    @endif
                    <div class="wc-right">
                        @if (! $c)
                            <x-filament::button wire:click="openContract({{ $r['id'] }})" size="sm" color="primary" icon="heroicon-o-document-plus">
                                إنشاء مسوّدة العقد
                            </x-filament::button>
                        @else
                            @unless ($c['is_approved'])
                                <x-filament::button wire:click="openContract({{ $r['id'] }})" size="sm" color="gray" icon="heroicon-o-pencil-square">
                                    تحرير البنود
                                </x-filament::button>
                                @if ($status === 'draft')
                                    <x-filament::button wire:click="sendContract({{ $r['id'] }})" size="sm" color="primary" icon="heroicon-o-paper-airplane"
                                                        wire:confirm="ستُرسل مسوّدة العقد إلى بريد العميل الإلكتروني لمراجعة بنودها. متابعة؟">
                                        إرسال المسوّدة للمراجعة
                                    </x-filament::button>
                                @else
                                    <x-filament::button wire:click="sendContract({{ $r['id'] }})" size="sm" color="warning" icon="heroicon-o-arrow-path"
                                                        wire:confirm="ستُفتح جولة مراجعة جديدة: البنود المعتمدة تبقى معتمدة، وغيرها يُعرض على العميل للقرار من جديد. متابعة؟">
                                        إعادة الإرسال بعد التعديل
                                    </x-filament::button>
                                @endif
                                <x-filament::button wire:click="deleteContract({{ $r['id'] }})" size="sm" color="danger" icon="heroicon-o-trash"
                                                    wire:confirm="سيُحذف العقد {{ $c['number'] }} وقرارات العميل عليه، وتُنشأ مسوّدة جديدة برقم جديد عند الحاجة. متابعة؟">
                                    حذف المسوّدة
                                </x-filament::button>
                            @else
                                <x-filament::badge color="success" size="lg">اعتمد العميل جميع البنود — تُرسل له النسخة الموقّعة من الشركة للتوقيع</x-filament::badge>
                            @endunless
                        @endif
                    </div>
                </div>

                @if ($this->open === $r['id'])
                    <div class="wc-editor" wire:key="editor-{{ $r['id'] }}">
                        <h3>بنود العقد {{ $c['number'] ?? '' }}</h3>
                        <p class="sub">
                            عدّل العنوان والنص، أضف بنوداً أو احذفها أو رتّبها.
                            <b>تعديل نص بند اعتمده العميل يُلغي اعتماده</b> ويُطلب قراره فيه من جديد عند إعادة الإرسال.
                        </p>

                        <div class="wc-clauses">
                            @foreach ($this->clauses as $i => $clause)
                                <div @class(['wc-clause', 'is-locked' => $clause['locked'], 'is-edit' => $clause['decision'] === 'edited', 'is-del' => $clause['decision'] === 'deleted'])
                                     wire:key="clause-{{ $r['id'] }}-{{ $i }}">
                                    <div class="wc-clause-top">
                                        <span class="wc-clause-no">{{ $i + 1 }}</span>
                                        <input type="text" class="wc-in" placeholder="القسم (تمهيد، الالتزامات…)" wire:model="clauses.{{ $i }}.section">
                                        <input type="text" class="wc-in" placeholder="عنوان البند" wire:model="clauses.{{ $i }}.title">
                                        <div class="wc-clause-state">
                                            @if ($clause['locked'])
                                                <x-filament::badge color="success">اعتمده العميل</x-filament::badge>
                                            @elseif ($clause['decision'] === 'edited')
                                                <x-filament::badge color="warning">طلب تعديله</x-filament::badge>
                                            @elseif ($clause['decision'] === 'deleted')
                                                <x-filament::badge color="danger">طلب حذفه</x-filament::badge>
                                            @endif
                                            <div class="wc-tools">
                                                <button type="button" class="wc-btn" wire:click="moveClause({{ $i }}, -1)" @disabled($i === 0) title="لأعلى">↑</button>
                                                <button type="button" class="wc-btn" wire:click="moveClause({{ $i }}, 1)" @disabled($i === count($this->clauses) - 1) title="لأسفل">↓</button>
                                                <button type="button" class="wc-btn del" wire:click="removeClause({{ $i }})" title="حذف البند"
                                                        wire:confirm="سيُحذف هذا البند من العقد. متابعة؟">✕</button>
                                            </div>
                                        </div>
                                    </div>
                                    <textarea class="wc-in" placeholder="نص البند" wire:model="clauses.{{ $i }}.body"></textarea>
                                    @if ($clause['note'] !== '' && in_array($clause['decision'], ['edited', 'deleted'], true))
                                        <div class="wc-note"><b>{{ $clause['decision_label'] }} — العميل يقول:</b> {{ $clause['note'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="wc-editor-actions">
                            <x-filament::button wire:click="addClause" size="sm" color="gray" icon="heroicon-o-plus">إضافة بند</x-filament::button>
                            <div class="wc-right">
                                <x-filament::button wire:click="closeContract" size="sm" color="gray">إغلاق دون حفظ</x-filament::button>
                                <x-filament::button wire:click="saveClauses(false)" size="sm" color="primary" icon="heroicon-o-check">حفظ البنود</x-filament::button>
                                <x-filament::button wire:click="saveClauses(true)" size="sm" color="success" icon="heroicon-o-paper-airplane"
                                                    wire:confirm="{{ ($c['round'] ?? 0) > 0 ? 'ستُحفظ البنود ويُعاد إرسال العقد للعميل بعد التعديل. متابعة؟' : 'ستُحفظ البنود وتُرسل مسوّدة العقد للعميل لمراجعتها. متابعة؟' }}">
                                    {{ ($c['round'] ?? 0) > 0 ? 'حفظ وإعادة الإرسال بعد التعديل' : 'حفظ وإرسال المسوّدة للمراجعة' }}
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
