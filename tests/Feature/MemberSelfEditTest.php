<?php

namespace Tests\Feature;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Filament\Member\Resources\MemberAccounts\Pages\EditMemberAccount;
use App\Models\Member;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberSelfEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_edit_own_record_and_status_moves_to_processing(): void
    {
        $this->actingAsMember('family@example.com');
        $member = $this->makeMember(['email' => 'family@example.com', 'status' => 'active']);

        Livewire::test(EditMemberAccount::class, ['record' => $member->member_number])
            ->fillForm(['full_name' => 'Neuer Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $member->refresh();
        $this->assertSame('Neuer Name', $member->full_name);
        $this->assertSame('processing', $member->status);
    }

    public function test_no_op_save_keeps_status_unchanged(): void
    {
        $this->actingAsMember('family@example.com');
        $member = $this->makeMember(['email' => 'family@example.com', 'status' => 'active']);

        Livewire::test(EditMemberAccount::class, ['record' => $member->member_number])
            ->call('save')
            ->assertHasNoFormErrors();

        $member->refresh();
        $this->assertSame('active', $member->status);
    }

    public function test_forged_protected_fields_are_ignored_on_save(): void
    {
        $this->actingAsMember('family@example.com');
        $member = $this->makeMember(['email' => 'family@example.com', 'status' => 'pending']);
        $originalNumber = $member->member_number;

        Livewire::test(EditMemberAccount::class, ['record' => $member->member_number])
            ->fillForm(['full_name' => 'Geändert'])
            ->set('data.status', 'active')
            ->set('data.admin_notiz', 'hacked')
            ->set('data.member_number', 'DA-9999-9999')
            ->set('data.email', 'attacker@example.com')
            ->call('save')
            ->assertHasNoFormErrors();

        $member->refresh();
        $this->assertSame('Geändert', $member->full_name);
        $this->assertSame('processing', $member->status, 'forged status must be ignored');
        $this->assertNull($member->admin_notiz);
        $this->assertSame($originalNumber, $member->member_number);
        $this->assertSame('family@example.com', $member->email);
    }

    public function test_switching_to_lastschrift_without_consent_is_blocked(): void
    {
        $this->actingAsMember('family@example.com');
        $member = $this->makeMember([
            'email' => 'family@example.com',
            'status' => 'active',
            'zahlungsart' => 'barzahlung',
            'sepa_zustimmung' => false,
        ]);

        Livewire::test(EditMemberAccount::class, ['record' => $member->member_number])
            ->fillForm([
                'zahlungsart' => 'lastschrift',
                'kontoinhaber' => 'Max Mustermann',
                'iban' => 'DE89370400440532013000',
            ])
            ->call('save')
            ->assertHasFormErrors(['zahlungsart']);

        $member->refresh();
        $this->assertSame('barzahlung', $member->zahlungsart);
    }

    public function test_member_cannot_open_or_edit_inactive_record(): void
    {
        $this->actingAsMember('family@example.com');
        $inactive = $this->makeMember(['email' => 'family@example.com', 'status' => 'inactive']);

        $this->get(MemberAccountResource::getUrl('view', ['record' => $inactive], panel: 'member'))
            ->assertForbidden();
        $this->get(MemberAccountResource::getUrl('edit', ['record' => $inactive], panel: 'member'))
            ->assertForbidden();
    }

    public function test_inactive_record_is_listed_but_dimmed(): void
    {
        $this->actingAsMember('family@example.com');
        $active = $this->makeMember(['email' => 'family@example.com', 'status' => 'active', 'full_name' => 'Aktiv Person']);
        $inactive = $this->makeMember(['email' => 'family@example.com', 'status' => 'inactive', 'full_name' => 'Inaktiv Person']);

        $this->get(MemberAccountResource::getUrl(panel: 'member'))
            ->assertOk()
            ->assertSee($active->member_number)
            ->assertSee($inactive->member_number)
            ->assertSee('opacity-50', false);
    }

    public function test_member_cannot_edit_record_of_another_email(): void
    {
        $this->actingAsMember('family@example.com');
        $other = $this->makeMember(['email' => 'other@example.com', 'status' => 'active']);

        $this->get(MemberAccountResource::getUrl('edit', ['record' => $other], panel: 'member'))
            ->assertNotFound();
    }

    private function actingAsMember(string $email): void
    {
        Filament::setCurrentPanel(Filament::getPanel('member'));
        $this->actingAs(User::create([
            'name' => 'Member',
            'email' => $email,
            'password' => 'secret',
        ]));
    }

    private function makeMember(array $attributes = []): Member
    {
        return Member::create(array_merge([
            'anrede' => 'Herr',
            'full_name' => 'Max Mustermann',
            'street' => 'Musterstrasse 1',
            'city' => 'Ahlen',
            'state' => 'Nordrhein-Westfalen',
            'postal_code' => '59227',
            'birth_date' => '1990-01-01',
            'email' => 'max@example.com',
            'phone' => '+492382123456',
            'zahlungsart' => 'barzahlung',
            'monatsbeitrag' => 25,
            'unterschrift' => '',
            'dsgvo_zustimmung' => true,
            'status' => 'pending',
        ], $attributes));
    }
}
