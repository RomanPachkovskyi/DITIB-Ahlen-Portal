<?php

namespace Tests\Feature;

use App\Support\MemberFieldRules;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MemberFieldRulesTest extends TestCase
{
    /**
     * @param  array<int, mixed>  $rules
     */
    private function fails(array $rules, mixed $value): bool
    {
        return Validator::make(['field' => $value], ['field' => $rules])->fails();
    }

    public function test_full_name_allows_letters_and_rejects_digits(): void
    {
        $this->assertFalse($this->fails(MemberFieldRules::fullName(), 'Çağla Müller-Öz'));
        $this->assertTrue($this->fails(MemberFieldRules::fullName(), 'Max123'));
        $this->assertTrue($this->fails(MemberFieldRules::fullName(), ''));
    }

    public function test_birth_date_enforces_minimum_age(): void
    {
        $this->assertTrue($this->fails(MemberFieldRules::birthDate(), now()->subYears(15)->format('Y-m-d')));
        $this->assertFalse($this->fails(MemberFieldRules::birthDate(), now()->subYears(16)->format('Y-m-d')));
    }

    public function test_phone_rule_uses_phone_number_validity(): void
    {
        $this->assertFalse($this->fails(MemberFieldRules::phone(), '+49 2382 123456'));
        $this->assertTrue($this->fails(MemberFieldRules::phone(), 'not-a-phone'));
    }

    public function test_iban_rule_checks_structure(): void
    {
        $this->assertFalse($this->fails(MemberFieldRules::iban(), 'DE89 3704 0044 0532 0130 00'));
        $this->assertTrue($this->fails(MemberFieldRules::iban(), 'INVALID'));
    }

    public function test_postal_code_requires_five_digits(): void
    {
        $this->assertFalse($this->fails(MemberFieldRules::postalCode(), '59227'));
        $this->assertTrue($this->fails(MemberFieldRules::postalCode(), '592'));
    }

    public function test_monatsbeitrag_enforces_minimum(): void
    {
        $this->assertTrue($this->fails(MemberFieldRules::monatsbeitrag(), 9.99));
        $this->assertFalse($this->fails(MemberFieldRules::monatsbeitrag(), 10));
    }
}
