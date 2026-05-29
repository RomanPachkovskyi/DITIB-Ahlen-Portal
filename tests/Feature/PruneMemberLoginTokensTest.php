<?php

namespace Tests\Feature;

use App\Models\MemberLoginToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneMemberLoginTokensTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prunes_used_and_expired_tokens_but_keeps_active_ones(): void
    {
        $active = $this->makeToken([
            'token_hash' => str_repeat('a', 64),
            'expires_at' => now()->addHour(),
            'used_at' => null,
        ]);

        $used = $this->makeToken([
            'token_hash' => str_repeat('b', 64),
            'expires_at' => now()->addHour(),
            'used_at' => now()->subMinute(),
        ]);

        $expired = $this->makeToken([
            'token_hash' => str_repeat('c', 64),
            'expires_at' => now()->subMinute(),
            'used_at' => null,
        ]);

        $this->artisan('member:prune-login-tokens')
            ->expectsOutputToContain('Pruned 2 used/expired member login token(s).')
            ->assertSuccessful();

        $this->assertDatabaseHas('member_login_tokens', ['id' => $active->id]);
        $this->assertDatabaseMissing('member_login_tokens', ['id' => $used->id]);
        $this->assertDatabaseMissing('member_login_tokens', ['id' => $expired->id]);
    }

    public function test_keep_hours_retains_recently_resolved_tokens(): void
    {
        $recentlyUsed = $this->makeToken([
            'token_hash' => str_repeat('d', 64),
            'expires_at' => now()->addHour(),
            'used_at' => now()->subHours(2),
        ]);

        $oldUsed = $this->makeToken([
            'token_hash' => str_repeat('e', 64),
            'expires_at' => now()->subHours(30),
            'used_at' => now()->subHours(30),
        ]);

        $this->artisan('member:prune-login-tokens', ['--keep-hours' => 24])
            ->assertSuccessful();

        $this->assertDatabaseHas('member_login_tokens', ['id' => $recentlyUsed->id]);
        $this->assertDatabaseMissing('member_login_tokens', ['id' => $oldUsed->id]);
    }

    private function makeToken(array $attributes): MemberLoginToken
    {
        return MemberLoginToken::create(array_merge([
            'email' => 'family@example.com',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ], $attributes));
    }
}
