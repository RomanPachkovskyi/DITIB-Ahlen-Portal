<?php

namespace Tests\Unit;

use App\Filament\Resources\Members\Schemas\MemberFormContext;
use App\Models\Member;
use PHPUnit\Framework\TestCase;

class MemberFormContextTest extends TestCase
{
    public function test_editable_and_protected_fields_do_not_overlap(): void
    {
        $overlap = array_intersect(
            MemberFormContext::memberEditableFields(),
            MemberFormContext::memberProtectedFields(),
        );

        $this->assertSame([], array_values($overlap));
    }

    public function test_sensitive_and_admin_fields_are_protected(): void
    {
        $protected = MemberFormContext::memberProtectedFields();

        foreach ([
            'status',
            'admin_notiz',
            'member_number',
            'email',
            'sepa_zustimmung',
            'dsgvo_zustimmung',
            'zustimmung_at',
            'profile_photo_zustimmung',
            'profile_photo_path',
            'unterschrift',
            'id',
        ] as $field) {
            $this->assertContains($field, $protected, "$field must not be member-editable");
        }
    }

    public function test_every_member_attribute_is_either_editable_or_protected(): void
    {
        $fillable = (new Member())->getFillable();

        $covered = array_merge(
            MemberFormContext::memberEditableFields(),
            MemberFormContext::memberProtectedFields(),
        );

        $uncovered = array_diff($fillable, $covered);

        $this->assertSame([], array_values($uncovered), 'New fillable fields are protected by default; allowlist explicitly to make editable.');
    }

    public function test_only_member_editable_strips_forbidden_keys(): void
    {
        $payload = [
            'full_name' => 'Neuer Name',
            'phone' => '+492382123456',
            'iban' => 'DE89370400440532013000',
            // forged / forbidden keys:
            'status' => 'active',
            'admin_notiz' => 'hacked',
            'member_number' => 'DA-9999-9999',
            'email' => 'attacker@example.com',
            'sepa_zustimmung' => true,
            'id' => 1,
        ];

        $clean = MemberFormContext::onlyMemberEditable($payload);

        $this->assertSame(['full_name', 'phone', 'iban'], array_keys($clean));
        $this->assertArrayNotHasKey('status', $clean);
        $this->assertArrayNotHasKey('admin_notiz', $clean);
        $this->assertArrayNotHasKey('member_number', $clean);
        $this->assertArrayNotHasKey('email', $clean);
        $this->assertArrayNotHasKey('sepa_zustimmung', $clean);
    }
}
