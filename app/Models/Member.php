<?php

namespace App\Models;

use App\Mail\MemberApprovedNotification;
use App\Mail\MemberDeletedAdminNotification;
use App\Mail\MemberDeletedNotification;
use App\Services\MemberAuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'anrede',
        'member_number',
        'full_name',
        'street',
        'city',
        'state',
        'postal_code',
        'birth_date',
        'birth_place',
        'staatsangehoerigkeit',
        'familienangehoerige',
        'cenaze_fonu',
        'cenaze_fonu_nr',
        'gemeinderegister',
        'beruf',
        'heimatstadt',
        'email',
        'phone',
        'instagram',
        'profile_photo_path',
        'profile_photo_uploaded_at',
        'profile_photo_zustimmung',
        'profile_photo_zustimmung_at',
        'zahlungsart',
        'monatsbeitrag',
        'kontoinhaber',
        'iban',
        'bic',
        'kreditinstitut',
        'unterschrift',
        'sepa_zustimmung',
        'sepa_zustimmung_at',
        'dsgvo_zustimmung',
        'zustimmung_at',
        'status',
        'admin_notiz',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'zustimmung_at' => 'datetime',
        'sepa_zustimmung' => 'boolean',
        'sepa_zustimmung_at' => 'datetime',
        'dsgvo_zustimmung' => 'boolean',
        'monatsbeitrag' => 'decimal:2',
        'cenaze_fonu' => 'boolean',
        'gemeinderegister' => 'boolean',
        'familienangehoerige' => 'integer',
        'profile_photo_uploaded_at' => 'datetime',
        'profile_photo_zustimmung' => 'boolean',
        'profile_photo_zustimmung_at' => 'datetime',
        'iban' => 'encrypted',
        'bic' => 'encrypted',
    ];

    protected $hidden = ['unterschrift'];

    public function getRouteKeyName(): string
    {
        return 'member_number';
    }

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            if (empty($member->member_number)) {
                $member->member_number = MemberNumberSequence::issueForMembers();
            }
        });

        // Domain invariant: a SEPA mandate and bank data only exist while the
        // payment method is Lastschrift. Switching to Barzahlung/Dauerauftrag
        // (admin, member or public form) clears them — no stale mandate, less PII.
        static::saving(function (Member $member) {
            if ($member->zahlungsart !== 'lastschrift') {
                $member->sepa_zustimmung = false;
                $member->sepa_zustimmung_at = null;
                $member->iban = null;
                $member->bic = null;
                $member->kontoinhaber = null;
                $member->kreditinstitut = null;
            }
        });

        static::updated(function (Member $member) {
            if (! $member->wasChanged('status') || $member->status !== 'active' || $member->getOriginal('status') === 'active') {
                return;
            }

            try {
                Mail::to($member->email)->send(new MemberApprovedNotification($member));
            } catch (Throwable $exception) {
                Log::error('Member approval email delivery failed.', [
                    'member_id' => $member->id,
                    'member_number' => $member->member_number,
                    'email' => $member->email,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);
            }
        });

        static::deleting(function (Member $member) {
            app(MemberAuditLogger::class)->deleted($member);

            try {
                Mail::to('info@ditib-ahlen-projekte.de')->send(new MemberDeletedAdminNotification($member));
            } catch (Throwable $exception) {
                Log::error('Member deletion admin email delivery failed.', [
                    'member_id' => $member->id,
                    'member_number' => $member->member_number,
                    'email' => $member->email,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);
            }

            try {
                Mail::to($member->email)->send(new MemberDeletedNotification($member));
            } catch (Throwable $exception) {
                Log::error('Member deletion email delivery failed.', [
                    'member_id' => $member->id,
                    'member_number' => $member->member_number,
                    'email' => $member->email,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);
            }
        });
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(MemberAuditLog::class);
    }
}
