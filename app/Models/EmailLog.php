<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'member_id',
        'event',
        'mail_class',
        'recipient_type',
        'recipient_email',
        'status',
        'error_message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function eventLabel(): string
    {
        return match ($this->event) {
            'registration_confirmation' => 'Registrierungsbestätigung',
            'admin_new_member'          => 'Neues Mitglied (Admin)',
            'member_approved'           => 'Mitgliedschaft bestätigt',
            'member_deleted'            => 'Mitgliedschaft gelöscht',
            'admin_member_deleted'      => 'Mitglied gelöscht (Admin)',
            'admin_member_updated'      => 'Datenänderung (Admin)',
            'login_link'                => 'Login-Link',
            default                     => $this->event,
        };
    }

    public function recipientTypeLabel(): string
    {
        return $this->recipient_type === 'admin' ? 'Admin' : 'Mitglied';
    }
}
