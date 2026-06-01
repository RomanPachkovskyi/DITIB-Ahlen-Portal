<?php

namespace Tests\Feature;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Filament\Member\Resources\MemberAccounts\Pages\EditMemberAccount;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Pages\ListMembers;
use App\Models\Member;
use App\Models\MemberAuditLog;
use App\Models\User;
use App\Services\MemberAuditLogger;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_edit_creates_member_visible_audit_log(): void
    {
        $this->actingAsMember('family@example.com');
        $member = $this->makeMember(['email' => 'family@example.com', 'status' => 'active']);

        Livewire::test(EditMemberAccount::class, ['record' => $member->member_number])
            ->fillForm(['phone' => '+49 2382 654321'])
            ->call('save')
            ->assertHasNoFormErrors();

        $log = MemberAuditLog::query()->firstOrFail();

        $this->assertSame($member->id, $log->member_id);
        $this->assertSame('member', $log->actor_type);
        $this->assertSame('Telefonnummer geändert', $log->description);

        $this->get(MemberAccountResource::getUrl('logs', ['record' => $member], panel: 'member'))
            ->assertOk()
            ->assertSee('ditib-audit-timeline', false)
            ->assertSee('- Telefonnummer geändert')
            ->assertSee('Kunde');
    }

    public function test_admin_status_action_creates_account_confirmation_log(): void
    {
        $this->actingAsAdmin();
        $member = $this->makeMember(['status' => 'pending']);

        Livewire::test(ListMembers::class)
            ->callTableAction('set_status_active', $member);

        $log = MemberAuditLog::query()->firstOrFail();

        $this->assertSame('admin', $log->actor_type);
        $this->assertSame('status_changed', $log->event);
        $this->assertSame('Account bestätigt', $log->description);
    }

    public function test_audit_log_masks_sensitive_bank_values(): void
    {
        $member = $this->makeMember();

        app(MemberAuditLogger::class)->memberUpdated($member, [
            'iban' => 'DE89370400440532013000',
            'bic' => 'COBADEFFXXX',
        ], 'member');

        $log = MemberAuditLog::query()->firstOrFail();

        $this->assertSame('****3000', $log->new_values['iban']);
        $this->assertSame('****FXXX', $log->new_values['bic']);
        $this->assertStringNotContainsString('DE89370400440532013000', json_encode($log->new_values));
    }

    public function test_admin_form_links_to_record_logs(): void
    {
        $this->actingAsAdmin();
        $member = $this->makeMember(['email' => 'family@example.com', 'status' => 'active']);

        $this->get(MemberResource::getUrl('view', ['record' => $member], panel: 'admin'))
            ->assertOk()
            ->assertSee('Historie dieses Eintrags anzeigen');
    }

    public function test_member_form_links_to_record_logs(): void
    {
        $this->actingAsMember('family@example.com');
        $member = $this->makeMember(['email' => 'family@example.com', 'status' => 'active']);

        $this->get(MemberAccountResource::getUrl('view', ['record' => $member], panel: 'member'))
            ->assertOk()
            ->assertSee('Historie dieses Eintrags anzeigen');
    }

    private function actingAsAdmin(): User
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'info@ditib-ahlen-projekte.de',
            'password' => 'secret',
        ]);

        $this->actingAs($admin);

        return $admin;
    }

    private function actingAsMember(string $email): User
    {
        Filament::setCurrentPanel(Filament::getPanel('member'));

        $user = User::create([
            'name' => 'Member',
            'email' => $email,
            'password' => 'secret',
        ]);

        $this->actingAs($user);

        return $user;
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
