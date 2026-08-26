<?php

namespace App\Models;

use App\Observers\ServiceRequestObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ServiceRequestObserver::class])]
class ServiceRequest extends Model
{
    protected $fillable = [
        'service_id', 'service_type', 'name', 'phone', 'email', 'company',
        'budget', 'message', 'payload', 'status', 'assigned_to', 'source',
        'ip', 'user_agent', 'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'contacted_at' => 'datetime',
        ];
    }

    public const STATUSES = ['new', 'contacted', 'qualified', 'proposal', 'won', 'lost'];

    public const TYPES = ['ecommerce', 'tech_solution', 'training', 'general'];

    /**
     * أول رقم مرجعي معروض. الطلب رقم 1 يحمل WRD-YYYY-MM-DD-00101.
     * غيّر الرقم هنا إن أردت بدء الترقيم من قيمة أخرى.
     */
    public const REFERENCE_BASE = 100;

    /**
     * الرقم المرجعي الرسمي للطلب: WRD-2026-08-26-00115
     * يُشتق من تاريخ الطلب ومعرّفه، فهو ثابت ولا يحتاج عموداً في قاعدة البيانات.
     */
    protected function reference(): Attribute
    {
        return Attribute::get(fn (): string => sprintf(
            'WRD-%s-%05d',
            ($this->created_at ?? now())->format('Y-m-d'),
            (int) $this->id + self::REFERENCE_BASE,
        ));
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
