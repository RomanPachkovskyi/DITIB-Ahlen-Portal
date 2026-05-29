<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Carbon;

/**
 * Single source of truth for Member field validation rules.
 *
 * Normalization already lives in dedicated helpers (PhoneNumber, Iban,
 * Instagram); this class holds the per-field validation rules — including the
 * closure rules (minimum age, phone, IBAN, Instagram) — so the public Livewire
 * registration form and the future Filament admin/member edit forms validate
 * identically. Without this, member self-edit could bypass checks that only
 * lived inline in MembershipForm (plan item E).
 *
 * UX messages stay with each form (the public form has German step messages,
 * Filament has its own); the closure rules carry their own German fail text so
 * they are self-contained when reused.
 */
class MemberFieldRules
{
    /** Letters (any language), spaces and hyphens only. */
    public const NAME_REGEX = 'regex:/^[\pL\s\-]+$/u';

    public const MIN_MONATSBEITRAG = 10;

    public const MIN_AGE = 16;

    /** @return array<int, mixed> */
    public static function anrede(): array
    {
        return ['required', 'in:Frau,Herr'];
    }

    /** @return array<int, mixed> */
    public static function fullName(): array
    {
        return ['required', 'string', 'max:255', self::NAME_REGEX];
    }

    /** @return array<int, mixed> */
    public static function birthDate(): array
    {
        return ['required', 'date', 'before:today', self::minimumAgeRule()];
    }

    /** @return array<int, mixed> */
    public static function birthPlace(): array
    {
        return ['nullable', 'string', 'max:255', self::NAME_REGEX];
    }

    /** @return array<int, mixed> */
    public static function staatsangehoerigkeit(): array
    {
        return ['nullable', 'string', 'max:100', self::NAME_REGEX];
    }

    /** @return array<int, mixed> */
    public static function familienangehoerige(): array
    {
        return ['required', 'integer', 'min:1'];
    }

    /** @return array<int, mixed> */
    public static function beruf(): array
    {
        return ['nullable', 'string', 'max:255', self::NAME_REGEX];
    }

    /** @return array<int, mixed> */
    public static function heimatstadt(): array
    {
        return ['nullable', 'string', 'max:255', self::NAME_REGEX];
    }

    /** @return array<int, mixed> */
    public static function street(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /** @return array<int, mixed> */
    public static function postalCode(): array
    {
        return ['required', 'string', 'regex:/^[0-9]{5}$/'];
    }

    /** @return array<int, mixed> */
    public static function city(): array
    {
        return ['required', 'string', 'max:100', self::NAME_REGEX];
    }

    /** @return array<int, mixed> */
    public static function state(): array
    {
        return ['required', 'string', 'max:100', self::NAME_REGEX];
    }

    /** @return array<int, mixed> */
    public static function email(): array
    {
        // E-Mail не unique: один email може використовуватись для кількох членів родини/фірми.
        return ['required', 'email'];
    }

    /** @return array<int, mixed> */
    public static function phone(): array
    {
        return ['required', 'string', 'max:50', self::phoneRule()];
    }

    /** @return array<int, mixed> */
    public static function instagram(): array
    {
        return ['nullable', 'string', 'max:255', self::instagramRule()];
    }

    /** @return array<int, mixed> */
    public static function monatsbeitrag(): array
    {
        return ['required', 'numeric', 'min:'.self::MIN_MONATSBEITRAG];
    }

    /** @return array<int, mixed> */
    public static function zahlungsart(): array
    {
        return ['required', 'in:barzahlung,lastschrift,dauerauftrag'];
    }

    /** @return array<int, mixed> */
    public static function kontoinhaber(): array
    {
        return ['required', 'string', 'max:255', self::NAME_REGEX];
    }

    /** @return array<int, mixed> */
    public static function iban(): array
    {
        return ['required', 'string', self::ibanRule()];
    }

    /** @return array<int, mixed> */
    public static function bic(): array
    {
        return ['nullable', 'string', 'max:11'];
    }

    /** @return array<int, mixed> */
    public static function kreditinstitut(): array
    {
        return ['nullable', 'string', 'max:255', self::NAME_REGEX];
    }

    public static function minimumAgeRule(int $minAge = self::MIN_AGE): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($minAge): void {
            if (blank($value)) {
                return;
            }

            if (Carbon::parse($value)->age < $minAge) {
                $fail('Eine Registrierung ist erst ab '.$minAge.' Jahren möglich.');
            }
        };
    }

    public static function phoneRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! PhoneNumber::isValid($value)) {
                $fail(PhoneNumber::validationMessage());
            }
        };
    }

    public static function ibanRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! Iban::isValidStructure($value)) {
                $fail('Ungültige IBAN.');
            }
        };
    }

    public static function instagramRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! Instagram::isValid($value)) {
                $fail('Bitte geben Sie einen Instagram-Namen oder Instagram-Link ein.');
            }
        };
    }
}
