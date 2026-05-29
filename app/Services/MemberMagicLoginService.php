<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberLoginToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemberMagicLoginService
{
    public const EXPIRES_IN_MINUTES = 60;

    /**
     * @return array{token: MemberLoginToken, plain: string, url: string}|null
     */
    public function createForEmail(string $email, ?string $ipAddress = null, ?string $userAgent = null): ?array
    {
        $email = $this->normalizeEmail($email);

        if (! $this->memberEmailExists($email)) {
            return null;
        }

        $plainToken = Str::random(64);

        $token = MemberLoginToken::create([
            'email' => $email,
            'token_hash' => $this->hashToken($plainToken),
            'expires_at' => now()->addMinutes(self::EXPIRES_IN_MINUTES),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent !== null ? Str::limit($userAgent, 1000, '') : null,
        ]);

        return [
            'token' => $token,
            'plain' => $plainToken,
            'url' => route('member.magic-login.consume', ['token' => $plainToken]),
        ];
    }

    public function consume(string $plainToken): ?User
    {
        $token = MemberLoginToken::query()
            ->where('token_hash', $this->hashToken($plainToken))
            ->first();

        if (! $token?->isUsable()) {
            return null;
        }

        if (! $this->memberEmailExists($token->email)) {
            return null;
        }

        $token->forceFill(['used_at' => now()])->save();

        return User::firstOrCreate(
            ['email' => $token->email],
            [
                'name' => $token->email,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ],
        );
    }

    public function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function memberEmailExists(string $email): bool
    {
        return Member::query()
            ->whereRaw('lower(email) = ?', [$email])
            ->exists();
    }
}
