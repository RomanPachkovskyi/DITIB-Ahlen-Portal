<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Models\Member;
use Illuminate\Support\Arr;

/**
 * Single source of truth for member-card form modes.
 *
 * Phase 2 of docs/member-account-editing-audit-plan.md will hang the shared
 * schema-building logic (field visibility/editability per mode) off these
 * cases. This file already provides the security boundary for member self-edit
 * (plan item A): the server-side allowlist used by EditMemberAccount.
 */
enum MemberFormContext: string
{
    case AdminView = 'admin_view';
    case AdminEdit = 'admin_edit';
    case MemberView = 'member_view';
    case MemberEdit = 'member_edit';

    public function isMember(): bool
    {
        return $this === self::MemberView || $this === self::MemberEdit;
    }

    public function isEdit(): bool
    {
        return $this === self::AdminEdit || $this === self::MemberEdit;
    }

    /**
     * Fields a member may change via /konto in v1.
     *
     * This is an explicit allowlist. Anything not listed here is protected by
     * default, including fields added to Member in the future — they must be
     * added here deliberately before a member can edit them.
     *
     * @return array<int, string>
     */
    public static function memberEditableFields(): array
    {
        return [
            'full_name',
            'anrede',
            'birth_date',
            'birth_place',
            'staatsangehoerigkeit',
            'familienangehoerige',
            'beruf',
            'heimatstadt',
            'street',
            'postal_code',
            'city',
            'state',
            'phone',
            'instagram',
            'cenaze_fonu',
            'cenaze_fonu_nr',
            'gemeinderegister',
            'monatsbeitrag',
            'zahlungsart',
            'kontoinhaber',
            'iban',
            'bic',
            'kreditinstitut',
        ];
    }

    /**
     * Every member attribute a member may NOT change via /konto in v1.
     *
     * Derived (deny-by-default) so new mass-assignable or system columns are
     * automatically protected unless explicitly allowlisted above.
     *
     * @return array<int, string>
     */
    public static function memberProtectedFields(): array
    {
        $known = array_merge(
            (new Member())->getFillable(),
            ['id', 'created_at', 'updated_at', 'deleted_at'],
        );

        return array_values(array_diff($known, self::memberEditableFields()));
    }

    /**
     * Strip any non-allowlisted keys from a member-submitted payload.
     *
     * The security boundary for member self-edit: even if a forged Livewire
     * request carries status, admin_notiz, member_number, email or consent
     * fields, only allowlisted keys survive.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function onlyMemberEditable(array $data): array
    {
        return Arr::only($data, self::memberEditableFields());
    }
}
