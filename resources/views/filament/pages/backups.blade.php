<x-filament-panels::page>
    @php
        $rows = $this->rows;
        $fdt = fn ($d) => $d?->format('Y/m/d — H:i');
    @endphp

    {{-- أنماط الصفحة مضمّنة: لا تعتمد على بناء Tailwind الخاص بلوحة التحكم --}}
    <style>
        .wb-intro { border: 1px solid rgb(229 231 235); border-radius: .75rem; background: #fff; padding: 1rem 1.15rem; }
        .wb-intro h3 { font-size: .95rem; font-weight: 700; margin-bottom: .3rem; }
        .wb-intro p { font-size: .82rem; color: rgb(75 85 99); line-height: 1.9; }
        .wb-intro b { color: rgb(17 24 39); }
        .dark .wb-intro { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .dark .wb-intro p { color: rgb(212 212 216); }
        .dark .wb-intro b, .dark .wb-intro h3 { color: rgb(244 244 245); }

        .wb-take { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; margin-top: 1.25rem;
                   border: 1px solid rgb(191 219 254); border-radius: .75rem; background: rgb(248 250 255); padding: .85rem 1rem; }
        .wb-take input[type="text"] { flex: 1 1 18rem; min-width: 12rem; border: 1px solid rgb(209 213 219); border-radius: .5rem;
                                      padding: .45rem .7rem; font-size: .85rem; background: #fff; }
        .dark .wb-take { background: rgba(59,130,246,.06); border-color: rgba(59,130,246,.3); }
        .dark .wb-take input[type="text"] { background: rgb(39 39 42); border-color: rgb(63 63 70); color: rgb(244 244 245); }

        .wb-list { display: flex; flex-direction: column; gap: .7rem; margin-top: 1.25rem; }
        .wb-card { border: 1px solid rgb(229 231 235); border-radius: .75rem; background: #fff; padding: .8rem 1rem;
                   display: flex; flex-wrap: wrap; align-items: center; gap: .5rem 1rem; }
        .wb-card.is-latest { border-color: rgb(167 243 208); }
        .dark .wb-card { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .wb-name { font-family: ui-monospace, Menlo, monospace; font-size: .8rem; font-weight: 700; direction: ltr; color: rgb(37 99 235); }
        .wb-meta { font-size: .76rem; color: rgb(107 114 128); display: flex; flex-wrap: wrap; gap: .25rem .9rem; }
        .wb-meta b { color: rgb(17 24 39); font-weight: 600; }
        .dark .wb-meta b { color: rgb(244 244 245); }
        .wb-note { font-size: .76rem; color: rgb(180 83 9); background: rgb(254 252 232); border-radius: 99px; padding: .1rem .6rem; }
        .dark .wb-note { background: rgba(245,158,11,.12); }
        .wb-acts { margin-inline-start: auto; display: flex; flex-wrap: wrap; gap: .4rem; }
        .wb-col { display: flex; flex-direction: column; gap: .2rem; }

        .wb-empty { border: 1px dashed rgb(209 213 219); border-radius: .75rem; padding: 2.25rem 1rem; text-align: center;
                    color: rgb(107 114 128); font-size: .85rem; margin-top: 1.25rem; }

        .wb-up { margin-top: 1.5rem; border: 1px solid rgb(229 231 235); border-radius: .75rem; background: rgb(249 250 251); padding: .9rem 1rem; }
        .dark .wb-up { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .wb-up h3 { font-size: .88rem; font-weight: 700; margin-bottom: .2rem; }
        .wb-up p { font-size: .78rem; color: rgb(107 114 128); margin-bottom: .7rem; line-height: 1.8; }
        .wb-up-row { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; }
        .wb-err { font-size: .78rem; color: rgb(185 28 28); margin-top: .4rem; }

        /* المعرّفات اللاتينية داخل نصّ عربي: معزولة اتجاهياً وإلا قفزت النقطة إلى آخرها */
        .wb-intro code, .wb-up code { direction: ltr; unicode-bidi: isolate;
                                      font-family: ui-monospace, Menlo, monospace; font-size: .95em; }

        /* منتقي الملف: زرّ عربي بدل زرّ المتصفّح الإنجليزي */
        .wb-file { display: inline-flex; align-items: center; gap: .6rem; cursor: pointer; }
        .wb-file input[type="file"] { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
        .wb-file-btn { border: 1px solid rgb(209 213 219); border-radius: 99px; padding: .35rem .9rem;
                       font-size: .8rem; font-weight: 500; background: #fff; }
        .dark .wb-file-btn { background: rgb(39 39 42); border-color: rgb(63 63 70); }
        .wb-file-name { font-size: .78rem; color: rgb(107 114 128); overflow-wrap: anywhere; }

        @media (max-width: 900px) {
            .wb-acts { margin-inline-start: 0; width: 100%; }
        }
    </style>

    <div class="wb-intro">
        <h3>نسخة كاملة من بيانات الموقع، ورجوع إليها في أي وقت</h3>
        <p>
            كل نسخة ملفٌ مضغوط واحد يضمّ <b>قاعدة البيانات كاملة</b> (الطلبات وعروض الأسعار والعقود
            والخدمات والصفحات والإعدادات) و<b>كل ملفات العملاء</b> (متطلبات المشروع، النسخ الموقّعة من
            العقود، الختم، صور المتاجر والمنتجات).
            النسخ تُحفظ في مجلّد خاصّ على الخادم لا يصله أحد من الإنترنت، ويبقى منها أحدث
            {{ \App\Support\Backup::KEEP }} نسخة.
            <br>
            <b>نزّل النسخة إلى جهازك بعد أخذها</b> — نسخة على الخادم وحده تضيع مع الخادم.
            ولا تحوي النسخة ملف <code dir="ltr">.env</code> (فيه كلمات المرور، ومكانه الخادم وحده)
            ولا شيفرة المشروع (محفوظة في Git).
        </p>
    </div>

    <div class="wb-take">
        <input type="text" wire:model="note" placeholder="سبب هذه النسخة (اختياري) — مثل: قبل تعديل سجلّ الإصدارات">
        <x-filament::button wire:click="create" icon="heroicon-o-archive-box-arrow-down" wire:loading.attr="disabled">
            خذ نسخة احتياطية الآن
        </x-filament::button>
        <span wire:loading wire:target="create" style="font-size:.8rem;color:rgb(107 114 128)">جارٍ ضغط البيانات…</span>
    </div>

    @if (filled($rows))
        <div class="wb-list">
            @foreach ($rows as $i => $row)
                @php $m = $row['manifest'] ?? []; @endphp
                <div class="wb-card {{ $i === 0 ? 'is-latest' : '' }}" wire:key="bk-{{ $row['name'] }}">
                    <div class="wb-col">
                        <span class="wb-name">{{ $row['name'] }}</span>
                        <span class="wb-meta">
                            <span><b>{{ $fdt($row['created_at']) }}</b></span>
                            <span>{{ \App\Filament\Pages\Backups::size($row['size']) }}</span>
                            <span>{{ \App\Filament\Pages\Backups::summary($row) }}</span>
                            @if ($i === 0)<b style="color:rgb(5 150 105)">الأحدث</b>@endif
                        </span>
                    </div>

                    @if (filled($m['note'] ?? ''))
                        <span class="wb-note">{{ $m['note'] }}</span>
                    @endif

                    <div class="wb-acts">
                        <x-filament::button wire:click="download('{{ $row['name'] }}')" size="xs" color="gray"
                                            icon="heroicon-o-arrow-down-tray">تنزيل</x-filament::button>

                        <x-filament::button wire:click="restore('{{ $row['name'] }}')" size="xs" color="warning"
                                            icon="heroicon-o-arrow-uturn-left"
                                            wire:loading.attr="disabled" wire:target="restore"
                                            wire:confirm="سيعود الموقع بالكامل إلى لحظة هذه النسخة: كل طلب أو عرض أو عقد أو ملف أُضيف بعدها سيختفي.&#10;&#10;تُؤخذ نسخة أمان تلقائية قبل الاسترجاع فيمكن التراجع، وقد تحتاج إلى تسجيل الدخول من جديد بعده.&#10;&#10;متابعة؟">
                            استرجاع</x-filament::button>

                        <x-filament::button wire:click="remove('{{ $row['name'] }}')" size="xs" color="danger"
                                            icon="heroicon-o-trash" wire:confirm="سيُحذف ملف هذه النسخة نهائياً. متابعة؟">
                            حذف</x-filament::button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="wb-empty">لا نسخ محفوظة بعد — اضغط «خذ نسخة احتياطية الآن» لأخذ أول نسخة.</div>
    @endif

    <div class="wb-up">
        <h3>استرجاع من نسخة على جهازك</h3>
        <p>
            إن كان الخادم قد فُقد أو حُذفت نسخه، ارفع هنا ملف نسخة نزّلته سابقاً
            (<code dir="ltr">.zip</code>، حتى {{ (int) (\App\Support\Backup::UPLOAD_MAX_KB / 1024) }} ميغابايت)
            ليظهر في القائمة أعلاه جاهزاً للاسترجاع.
        </p>
        <div class="wb-up-row">
            <label class="wb-file" x-data="{ picked: '' }">
                <input type="file" accept=".zip,application/zip" wire:model="upload"
                       x-on:change="picked = $event.target.files[0]?.name ?? ''">
                <span class="wb-file-btn">اختر ملف النسخة</span>
                <span class="wb-file-name" x-text="picked || 'لم يُختر ملف بعد'"></span>
            </label>
            <x-filament::button wire:click="saveUpload" size="sm" color="gray" icon="heroicon-o-arrow-up-tray"
                                wire:loading.attr="disabled" wire:target="upload,saveUpload">
                ارفع الملف
            </x-filament::button>
            <span wire:loading wire:target="upload" style="font-size:.8rem;color:rgb(107 114 128)">جارٍ الرفع…</span>
        </div>
        @error('upload')<div class="wb-err">{{ $message }}</div>@enderror
    </div>
</x-filament-panels::page>
