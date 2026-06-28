<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * عزل بيانات المتاجر تلقائياً (دستور §1/§3: عزل المستأجرين بالبنية).
 * يُفعّل فقط عند ضبط currentStoreId (واجهة المتجر)، ولا يؤثّر على لوحة الأدمن.
 */
class StoreScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->bound('currentStoreId')) {
            $builder->where($model->getTable().'.store_id', app('currentStoreId'));
        }
    }
}
