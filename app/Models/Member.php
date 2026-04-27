<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'full_name',
        'street',
        'city',
        'state',
        'postal_code',
        'birth_date',
        'email',
        'phone',
        'jahresbeitrag',
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
        'birth_date'       => 'date',
        'zustimmung_at'    => 'datetime',
        'sepa_zustimmung'  => 'boolean',
        'dsgvo_zustimmung' => 'boolean',
        'jahresbeitrag'    => 'decimal:2',
        'iban'             => 'encrypted',
        'bic'              => 'encrypted',
    ];

    protected $hidden = ['unterschrift'];

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }
}
