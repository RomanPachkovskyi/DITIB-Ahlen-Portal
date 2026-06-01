<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'member_id',
        'actor_user_id',
        'actor_type',
        'actor_name',
        'event',
        'description',
        'changed_fields',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'changed_fields' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function actorLabel(): string
    {
        return match ($this->actor_type) {
            'admin' => 'Admin',
            'member', 'client' => 'Kunde',
            default => 'System',
        };
    }
}
