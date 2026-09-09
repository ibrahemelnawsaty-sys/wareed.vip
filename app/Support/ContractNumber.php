<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * مولّد رقم العقد التسلسلي: عدّاد عام واحد يشمل كل عقود النظام مهما كانت
 * الخدمة، ولا يُعاد تصفيره يومياً. يُدار بصفّ داخلي مستقل في جدول settings
 * بمعزل عن آلية الترجمة في Setting::get/set — فهو رقم صرف لا يُعرض كنص
 * متعدد اللغات — ويُقفَل صفّه أثناء الزيادة (SELECT ... FOR UPDATE داخل
 * معاملة) لمنع تصادم رقمين عند إنشاء عقدين في اللحظة نفسها.
 */
class ContractNumber
{
    private const KEY = 'contract_sequence';

    /** يزيد العدّاد ويعيد رقم العقد الجديد جاهزاً، مثل WRD-CTR-2026-09-09-00001. */
    public static function next(): string
    {
        $seq = DB::transaction(function () {
            $row = DB::table('settings')->where('key', self::KEY)->lockForUpdate()->first();
            $next = ($row ? (int) json_decode((string) $row->value, true) : 0) + 1;

            if ($row) {
                DB::table('settings')->where('key', self::KEY)->update([
                    'value' => json_encode($next), 'updated_at' => now(),
                ]);
            } else {
                DB::table('settings')->insert([
                    'key' => self::KEY, 'value' => json_encode($next),
                    'type' => 'text', 'group' => 'internal',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            return $next;
        });

        return sprintf('WRD-CTR-%s-%05d', now()->format('Y-m-d'), $seq);
    }
}
