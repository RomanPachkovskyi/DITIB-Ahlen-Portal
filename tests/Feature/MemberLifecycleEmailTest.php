<?php

namespace Tests\Feature;

use App\Mail\MemberApprovedNotification;
use App\Mail\MemberDeletedAdminNotification;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MemberLifecycleEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_emails_member_when_registration_is_approved(): void
    {
        Mail::fake();

        $member = $this->createMember([
            'email' => 'approved@example.com',
            'status' => 'pending',
        ]);

        $member->update(['status' => 'active']);

        Mail::assertSent(MemberApprovedNotification::class, 1);
        Mail::assertSent(MemberApprovedNotification::class, fn (MemberApprovedNotification $mail) => $mail->hasTo('approved@example.com'));
        Mail::assertNothingQueued();
    }

    public function test_it_does_not_email_member_when_active_status_is_saved_again(): void
    {
        Mail::fake();

        $member = $this->createMember([
            'email' => 'already-active@example.com',
            'status' => 'active',
        ]);

        $member->update(['full_name' => 'Ali Aktiv']);

        Mail::assertNotSent(MemberApprovedNotification::class);
    }

    public function test_it_emails_admin_when_member_is_deleted(): void
    {
        Mail::fake();

        $member = $this->createMember([
            'full_name' => 'Delete Test',
            'email' => 'delete-test@example.com',
            'status' => 'active',
        ]);

        $member->delete();

        Mail::assertSent(MemberDeletedAdminNotification::class, 1);
        Mail::assertSent(MemberDeletedAdminNotification::class, fn (MemberDeletedAdminNotification $mail) => $mail->hasTo('info@ditib-ahlen-projekte.de'));
        Mail::assertNothingQueued();
    }

    public function test_lifecycle_email_templates_render_member_details(): void
    {
        $member = $this->createMember([
            'full_name' => 'Template Test',
            'email' => 'template@example.com',
            'status' => 'active',
        ]);

        $this->assertStringContainsString('Template Test', (new MemberApprovedNotification($member))->render());
        $this->assertStringContainsString('Template Test', (new MemberDeletedAdminNotification($member))->render());
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createMember(array $overrides = []): Member
    {
        return Member::create(array_merge([
            'anrede' => 'Herr',
            'full_name' => 'Ali Mustermann',
            'street' => 'Musterstrasse 1',
            'city' => 'Ahlen',
            'state' => 'Nordrhein-Westfalen',
            'postal_code' => '59227',
            'birth_date' => '1990-01-01',
            'email' => 'ali@example.com',
            'phone' => '+492382123456',
            'zahlungsart' => 'barzahlung',
            'monatsbeitrag' => 25,
            'unterschrift' => '',
            'dsgvo_zustimmung' => true,
            'status' => 'pending',
        ], $overrides));
    }
}
