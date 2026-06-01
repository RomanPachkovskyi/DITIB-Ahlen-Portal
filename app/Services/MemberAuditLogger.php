<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberAuditLog;
use App\Models\User;
use App\Support\MemberStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MemberAuditLogger
{
    private const SENSITIVE_FIELDS = ['iban', 'bic'];

    /**
     * @var array<string, string>
     */
    private const FIELD_LABELS = [
        'anrede' => 'Anrede',
        'full_name' => 'Name',
        'birth_date' => 'Geburtsdatum',
        'birth_place' => 'Geburtsort',
        'staatsangehoerigkeit' => 'Staatsangehörigkeit',
        'familienangehoerige' => 'Familienangehörige',
        'cenaze_fonu' => 'Cenaze Fonu',
        'cenaze_fonu_nr' => 'Cenaze Fonu Nr.',
        'gemeinderegister' => 'Gemeinderegister',
        'beruf' => 'Beruf',
        'heimatstadt' => 'Heimatstadt',
        'street' => 'Adresse',
        'postal_code' => 'Postleitzahl',
        'city' => 'Ort',
        'state' => 'Bundesland',
        'email' => 'E-Mail',
        'phone' => 'Telefonnummer',
        'instagram' => 'Instagram',
        'profile_photo_path' => 'Profilfoto',
        'profile_photo_uploaded_at' => 'Profilfoto',
        'profile_photo_zustimmung' => 'Foto-Einwilligung',
        'profile_photo_zustimmung_at' => 'Foto-Einwilligung am',
        'zahlungsart' => 'Zahlungsweise',
        'monatsbeitrag' => 'Monatlicher Beitrag',
        'kontoinhaber' => 'Kontoinhaber',
        'iban' => 'IBAN',
        'bic' => 'BIC',
        'kreditinstitut' => 'Kreditinstitut',
        'sepa_zustimmung' => 'SEPA-Mandat',
        'sepa_zustimmung_at' => 'SEPA-Zustimmung am',
        'dsgvo_zustimmung' => 'Datenschutzerklärung',
        'zustimmung_at' => 'Zustimmung am',
        'status' => 'Status',
        'admin_notiz' => 'Interne Notiz',
    ];

    public function created(Member $member, string $actorType = 'client'): MemberAuditLog
    {
        return $this->write($member, 'member_created', 'Account erstellt', actorType: $actorType);
    }

    /**
     * Human-readable, mask-safe description of changed fields for notifications.
     * IBAN/BIC are masked (****1234); other fields show old → new.
     *
     * @param  array<string, array{old: mixed, new: mixed}>  $changes
     * @return array<int, array{label: string, old: ?string, new: ?string, sensitive: bool}>
     */
    public function describeChanges(array $changes): array
    {
        $out = [];

        foreach ($changes as $field => $pair) {
            $sensitive = in_array($field, self::SENSITIVE_FIELDS, true);

            $out[] = [
                'label' => self::FIELD_LABELS[$field] ?? Str::headline($field),
                'old' => $sensitive ? $this->maskSensitiveValue($pair['old'] ?? null) : $this->displayValue($field, $pair['old'] ?? null),
                'new' => $sensitive ? $this->maskSensitiveValue($pair['new'] ?? null) : $this->displayValue($field, $pair['new'] ?? null),
                'sensitive' => $sensitive,
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    public function memberUpdated(Member $member, array $changes, ?string $actorType = null): ?MemberAuditLog
    {
        $fields = $this->changedFieldNames($changes);

        if ($fields === []) {
            return null;
        }

        return $this->write($member, 'member_updated', $this->changedFieldsDescription($fields), [
            'changed_fields' => $this->labelsFor($fields),
            'new_values' => $this->valuesFor($changes),
        ], $actorType);
    }

    public function statusChanged(Member $member, ?string $oldStatus, string $newStatus, ?string $actorType = null): ?MemberAuditLog
    {
        if ($oldStatus === $newStatus) {
            return null;
        }

        return $this->write($member, 'status_changed', $this->statusDescription($newStatus), [
            'changed_fields' => ['Status'],
            'old_values' => ['status' => $oldStatus ? MemberStatus::label($oldStatus) : null],
            'new_values' => ['status' => MemberStatus::label($newStatus)],
        ], $actorType);
    }

    public function photoUploaded(Member $member, bool $replaced = false, ?string $actorType = null): MemberAuditLog
    {
        return $this->write(
            $member,
            $replaced ? 'profile_photo_replaced' : 'profile_photo_uploaded',
            $replaced ? 'Profilfoto ersetzt' : 'Profilfoto hochgeladen',
            ['changed_fields' => ['Profilfoto']],
            $actorType,
        );
    }

    public function photoDeleted(Member $member, ?string $actorType = null): MemberAuditLog
    {
        return $this->write($member, 'profile_photo_deleted', 'Profilfoto entfernt', [
            'changed_fields' => ['Profilfoto'],
        ], $actorType);
    }

    public function deleted(Member $member, ?string $actorType = null): MemberAuditLog
    {
        return $this->write($member, 'member_deleted', 'Account gelöscht', actorType: $actorType);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function write(Member $member, string $event, string $description, array $extra = [], ?string $actorType = null): MemberAuditLog
    {
        $actor = Auth::user();
        $actorType ??= $this->detectActorType($actor);

        // We deliberately do not store ip_address / user_agent: for this portal
        // it is enough to record WHO acted (admin vs member vs system). Less PII.
        return MemberAuditLog::create([
            'member_id' => $member->id,
            'actor_user_id' => $actor?->id,
            'actor_type' => $actorType,
            'actor_name' => $actor?->name ?? $actor?->email,
            'event' => $event,
            'description' => $description,
            ...$extra,
        ]);
    }

    private function detectActorType(?User $actor): string
    {
        if (! $actor) {
            return 'system';
        }

        return $actor->isAdmin() ? 'admin' : 'member';
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<int, string>
     */
    private function changedFieldNames(array $changes): array
    {
        return array_values(array_filter(array_keys($changes), fn (string $field): bool => ! in_array($field, [
            'created_at',
            'updated_at',
        ], true)));
    }

    /**
     * @param  array<int, string>  $fields
     * @return array<int, string>
     */
    private function labelsFor(array $fields): array
    {
        return array_map(fn (string $field): string => self::FIELD_LABELS[$field] ?? Str::headline($field), $fields);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function valuesFor(array $values): array
    {
        return collect($values)
            ->except(['created_at', 'updated_at'])
            ->mapWithKeys(fn (mixed $value, string $field): array => [
                $field => in_array($field, self::SENSITIVE_FIELDS, true)
                    ? $this->maskSensitiveValue($value)
                    : $this->stringValue($value),
            ])
            ->all();
    }

    /**
     * @param  array<int, string>  $fields
     */
    private function changedFieldsDescription(array $fields): string
    {
        $labels = $this->labelsFor($fields);

        if (count($labels) === 1) {
            return $labels[0].' geändert';
        }

        return 'Daten geändert: '.implode(', ', array_slice($labels, 0, 6)).(count($labels) > 6 ? ' ...' : '');
    }

    private function statusDescription(string $newStatus): string
    {
        return match ($newStatus) {
            MemberStatus::ACTIVE => 'Account bestätigt',
            MemberStatus::INACTIVE => 'Mitgliedschaft deaktiviert',
            MemberStatus::PROCESSING => 'Status auf Verarbeitung gesetzt',
            MemberStatus::PENDING => 'Status auf Neu gesetzt',
            default => 'Status geändert',
        };
    }

    private function maskSensitiveValue(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', '', (string) $value);

        if ($value === '') {
            return null;
        }

        return '****'.substr($value, -4);
    }

    private function stringValue(mixed $value): mixed
    {
        if ($value === null || is_bool($value) || is_numeric($value)) {
            return $value;
        }

        return (string) $value;
    }

    private function displayValue(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Ja' : 'Nein';
        }

        if ($value instanceof \DateTimeInterface) {
            return \Illuminate\Support\Carbon::instance($value)
                ->format($field === 'birth_date' ? 'd.m.Y' : 'd.m.Y H:i');
        }

        return (string) $value;
    }
}
