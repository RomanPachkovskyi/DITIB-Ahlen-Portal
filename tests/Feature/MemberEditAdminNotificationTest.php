<?php

namespace Tests\Feature;

use App\Filament\Member\Resources\MemberAccounts\Pages\EditMemberAccount;
use App\Mail\MemberUpdatedByMemberNotification;
use App\Models\Member;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MemberEditAdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_notified_when_member_edits_own_data(): void
    {
        Mail::fake();
        $this->actingAsMember('family@example.com');
        $member = $this->makeMember(['email' => 'family@example.com', 'status' => 'active']);

        Livewire::test(EditMemberAccount::class, ['record' => $member->member_number])
            ->fillForm(['phone' => '+49 2382 654321'])
            ->call('save')
            ->assertHasNoFormErrors();

        Mail::assertSent(MemberUpdatedByMemberNotification::class, function (MemberUpdatedByMemberNotification $mail) use ($member): bool {
            return $mail->hasTo('info@ditib-ahlen-projekte.de')
                && $mail->member->is($member)
                && collect($mail->changes)->contains(fn (array $c): bool => $c['label'] === 'Telefonnummer')
                && str_contains($mail->adminUrl, $member->member_number);
        });
    }

    public function test_no_op_save_sends_no_admin_email(): void
    {
        Mail::fake();
        $this->actingAsMember('family@example.com');
        $member = $this->makeMember(['email' => 'family@example.com', 'status' => 'active']);

        Livewire::test(EditMemberAccount::class, ['record' => $member->member_number])
            ->call('save')
            ->assertHasNoFormErrors();

        Mail::assertNothingSent();
    }

    public function test_iban_change_is_masked_in_admin_email(): void
    {
        Mail::fake();
        $this->actingAsMember('family@example.com');
        $member = $this->makeMember([
            'email' => 'family@example.com',
            'status' => 'active',
            'zahlungsart' => 'lastschrift',
            'sepa_zustimmung' => true,
            'kontoinhaber' => 'Max Mustermann',
            'iban' => 'DE89370400440532013000',
        ]);

        Livewire::test(EditMemberAccount::class, ['record' => $member->member_number])
            ->fillForm(['iban' => 'DE02120300000000202051'])
            ->set('data.sepa_reconsent', true)
            ->call('save')
            ->assertHasNoFormErrors();

        Mail::assertSent(MemberUpdatedByMemberNotification::class, function (MemberUpdatedByMemberNotification $mail): bool {
            $ibanChange = collect($mail->changes)->firstWhere('label', 'IBAN');

            return $ibanChange !== null
                && $ibanChange['new'] === '****2051'
                && ! str_contains(json_encode($mail->changes), 'DE02120300000000202051');
        });
    }

    public function test_notification_email_renders(): void
    {
        $member = $this->makeMember();
        $changes = [
            ['label' => 'Telefonnummer', 'old' => '+49 1', 'new' => '+49 2', 'sensitive' => false],
            ['label' => 'IBAN', 'old' => null, 'new' => '****2051', 'sensitive' => true],
        ];

        $html = (new MemberUpdatedByMemberNotification($member, $changes, 'https://example.test/admin'))->render();

        $this->assertStringContainsString('Geänderte Felder', $html);
        $this->assertStringContainsString('****2051', $html);
        $this->assertStringContainsString('Datensatz im Admin', $html);
        $this->assertStringContainsString($member->member_number, $html);
    }

    public function test_smtp_failure_does_not_break_save(): void
    {
        $this->actingAsMember('family@example.com');
        $member = $this->makeMember(['email' => 'family@example.com', 'status' => 'active']);

        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('smtp down'));

        Livewire::test(EditMemberAccount::class, ['record' => $member->member_number])
            ->fillForm(['full_name' => 'Neuer Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $member->refresh();
        $this->assertSame('Neuer Name', $member->full_name);
        $this->assertSame('processing', $member->status);
        $this->assertDatabaseHas('member_audit_logs', [
            'member_id' => $member->id,
            'event' => 'member_updated',
        ]);
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
