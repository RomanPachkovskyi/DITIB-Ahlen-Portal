<?php

namespace App\Models;

use App\Mail\MemberApprovedNotification;
use App\Mail\MemberDeletedAdminNotification;
use App\Mail\MemberDeletedNotification;
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
        'zahlungsart',
        'monatsbeitrag',
        'kontoinhaber',
        'iban',
        'bic',
        'kreditinstitut',
        'unterschrift',
        'sepa_zustimmung',
        'dsgvo_zustimmung',
        'zustimmung_at',
        'status',
        'admin_notiz',
    ];

    protected $casts = [
        'birth_date'          => 'date',
        'zustimmung_at'       => 'datetime',
        'sepa_zustimmung'     => 'boolean',
        'dsgvo_zustimmung'    => 'boolean',
        'monatsbeitrag'       => 'decimal:2',
        'cenaze_fonu'         => 'boolean',
        'gemeinderegister'    => 'boolean',
        'familienangehoerige' => 'integer',
        'iban'                => 'encrypted',
        'bic'                 => 'encrypted',
    ];

    protected $hidden = ['unterschrift'];

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            if (empty($member->member_number)) {
                $member->member_number = MemberNumberSequence::issueForMembers();
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
}
