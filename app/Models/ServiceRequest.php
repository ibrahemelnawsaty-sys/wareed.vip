<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
