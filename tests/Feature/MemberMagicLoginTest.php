<?php

namespace Tests\Feature;

use App\Filament\Member\Pages\Auth\RequestLoginLink;
use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Mail\MemberLoginLinkMail;
use App\Models\Member;
use App\Models\MemberLoginToken;
use App\Services\MemberMagicLoginService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MemberMagicLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_login_page_sends_link_for_registered_member_email(): void
    {
        Mail::fake();
        Filament::setCurrentPanel(Filament::getPanel('member'));
        $this->makeMember(['email' => 'Family@Example.com']);

        Livewire::test(RequestLoginLink::class)
            ->set('data.email', 'family@example.com')
            ->call('authenticate')
            ->assertSet('linkRequestSent', true);

        $this->assertDatabaseHas('member_login_tokens', [
            'email' => 'family@example.com',
            'used_at' => null,
        ]);

        Mail::assertSent(MemberLoginLinkMail::class, function (MemberLoginLinkMail $mail): bool {
            return str_contains($mail->loginUrl, '/konto/zugang/');
        });
    }

    public function test_member_login_page_does_not_reveal_unknown_email(): void
    {
        Mail::fake();
        Filament::setCurrentPanel(Filament::getPanel('member'));

        Livewire::test(RequestLoginLink::class)
            ->set('data.email', 'unknown@example.com')
            ->call('authenticate')
            ->assertSet('linkRequestSent', true);

        $this->assertDatabaseCount('member_login_tokens', 0);
        Mail::assertNothingSent();
    }

    public function test_valid_magic_link_logs_member_in_once(): void
    {
        $this->makeMember(['email' => 'family@example.com']);
        $link = app(MemberMagicLoginService::class)->createForEmail('family@example.com');

        $this->assertNotNull($link);

        $this->get($link['url'])
            ->assertRedirect(MemberAccountResource::getUrl(panel: 'member'));

        $this->assertAuthenticated();
        $this->assertSame('family@example.com', auth()->user()?->email);

        $this->get($link['url'])
            ->assertRedirect(Filament::getPanel('member')->getLoginUrl());
    }

    public function test_expired_magic_link_does_not_log_member_in(): void
    {
        $this->makeMember(['email' => 'family@example.com']);
        $plainToken = 'expired-token';

        MemberLoginToken::create([
            'email' => 'family@example.com',
            'token_hash' => app(MemberMagicLoginService::class)->hashToken($plainToken),
            'expires_at' => now()->subMinute(),
        ]);

        $this->get(route('member.magic-login.consume', ['token' => $plainToken]))
            ->assertRedirect(Filament::getPanel('member')->getLoginUrl());

        $this->assertGuest();
    }

    public function test_magic_link_opens_all_memberships_for_shared_email(): void
    {
        $first = $this->makeMember(['email' => 'family@example.com', 'full_name' => 'First Family']);
        $second = $this->makeMember(['email' => 'family@example.com', 'full_name' => 'Second Family']);
        $other = $this->makeMember(['email' => 'other@example.com', 'full_name' => 'Other Family']);
        $link = app(MemberMagicLoginService::class)->createForEmail('family@example.com');

        $this->get($link['url']);

        $this->get(MemberAccountResource::getUrl(panel: 'member'))
            ->assertOk()
            ->assertSee($first->member_number)
            ->assertSee($second->member_number)
            ->assertDontSee($other->member_number);
    }

    public function test_member_magic_login_does_not_grant_admin_access(): void
    {
        $this->makeMember(['email' => 'member@example.com']);
        $link = app(MemberMagicLoginService::class)->createForEmail('member@example.com');

        $this->get($link['url']);

        // Non-admin users are redirected to the admin login page (not 403).
        // The middleware also logs them out to prevent an infinite redirect loop
        // (Filament's Login::mount() would otherwise redirect()->intended()
        // straight back to the admin URL for any authenticated user).
        $this->get('/admin')
            ->assertRedirectToRoute('filament.admin.auth.login');

        $this->assertGuest();
    }

    public function test_no_magic_link_is_issued_for_admin_email_even_if_member_exists(): void
    {
        Mail::fake();
        Filament::setCurrentPanel(Filament::getPanel('member'));
        // Admin email is also present in members (shared-email scenario).
        $this->makeMember(['email' => 'info@ditib-ahlen-projekte.de']);

        $this->assertNull(
            app(MemberMagicLoginService::class)->createForEmail('Info@DITIB-Ahlen-Projekte.de')
        );

        // UI stays neutral, but no token is created and no mail is sent.
        Livewire::test(RequestLoginLink::class)
            ->set('data.email', 'info@ditib-ahlen-projekte.de')
            ->call('authenticate')
            ->assertSet('linkRequestSent', true);

        $this->assertDatabaseCount('member_login_tokens', 0);
        Mail::assertNothingSent();
    }

    public function test_pre_existing_admin_email_token_cannot_authenticate(): void
    {
        $this->makeMember(['email' => 'info@ditib-ahlen-projekte.de']);
        $plainToken = 'admin-email-token';

        MemberLoginToken::create([
            'email' => 'info@ditib-ahlen-projekte.de',
            'token_hash' => app(MemberMagicLoginService::class)->hashToken($plainToken),
            'expires_at' => now()->addMinutes(60),
        ]);

        $this->get(route('member.magic-login.consume', ['token' => $plainToken]))
            ->assertRedirect(Filament::getPanel('member')->getLoginUrl());

        $this->assertGuest();
    }

    public function test_issuing_a_new_token_revokes_previous_active_token(): void
    {
        $this->makeMember(['email' => 'active@example.com']);

        $previous = MemberLoginToken::create([
            'email' => 'active@example.com',
            'token_hash' => str_repeat('c', 64),
            'expires_at' => now()->addHour(),
        ]);

        app(MemberMagicLoginService::class)->createForEmail('active@example.com');

        $this->assertDatabaseMissing('member_login_tokens', ['id' => $previous->id]);
        $this->assertDatabaseCount('member_login_tokens', 1);
    }

    public function test_issuing_a_new_token_auto_prunes_spent_tokens(): void
    {
        $this->makeMember(['email' => 'family@example.com']);

        $used = MemberLoginToken::create([
            'email' => 'family@example.com',
            'token_hash' => str_repeat('a', 64),
            'expires_at' => now()->addHour(),
            'used_at' => now()->subMinute(),
        ]);

        $expired = MemberLoginToken::create([
            'email' => 'family@example.com',
            'token_hash' => str_repeat('b', 64),
            'expires_at' => now()->subMinute(),
        ]);

        $link = app(MemberMagicLoginService::class)->createForEmail('family@example.com');

        $this->assertNotNull($link);
        $this->assertDatabaseMissing('member_login_tokens', ['id' => $used->id]);
        $this->assertDatabaseMissing('member_login_tokens', ['id' => $expired->id]);
        // Only the freshly issued, still-active token remains.
        $this->assertDatabaseCount('member_login_tokens', 1);
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
