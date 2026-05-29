<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'email',
    'token_hash',
    'expires_at',
    'used_at',
    'ip_address',
    'user_agent',
])]
class MemberLoginToken extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function isUsable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /**
     * Delete used or expired tokens.
     *
     * These rows store ip_address and user_agent (PII) and have no further
     * purpose once spent, so we prune them to keep the table from becoming an
     * open PII archive. Called automatically whenever a new token is issued,
     * and also exposed via the member:prune-login-tokens command.
     */
    public static function pruneSpent(int $keepHours = 0): int
    {
        $threshold = now()->subHours(max(0, $keepHours));

        return static::query()
            ->where(function ($query) use ($threshold) {
                $query->whereNotNull('used_at')->where('used_at', '<=', $threshold);
            })
            ->orWhere('expires_at', '<=', $threshold)
            ->delete();
    }
}
