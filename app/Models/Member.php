<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
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
                $member->member_number = static::generateMemberNumber();
            }
        });
    }

    private static function generateMemberNumber(): string
    {
        $year = now()->format('Y');
        $last = static::whereYear('created_at', $year)
            ->whereNotNull('member_number')
            ->orderByDesc('id')
            ->value('member_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('DA-%s-%04d', $year, $seq);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }
}
