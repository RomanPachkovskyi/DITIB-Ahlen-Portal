<?php

namespace App\Console\Commands;

use App\Models\MemberLoginToken;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Removes spent member magic-link tokens.
 *
 * Routine cleanup already happens automatically: MemberMagicLoginService prunes
 * spent tokens every time a new one is issued (see MemberLoginToken::pruneSpent).
 * This command is an optional manual/ops fallback, e.g. for a one-off purge or a
 * custom --keep-hours window; it is not required for normal operation.
 */
#[Signature('member:prune-login-tokens {--keep-hours=0 : Retain used/expired tokens for this many hours before deleting}')]
#[Description('Deletes used or expired member magic-link tokens (removes stored IP/User-Agent PII)')]
class PruneMemberLoginTokens extends Command
{
    public function handle(): int
    {
        $deleted = MemberLoginToken::pruneSpent((int) $this->option('keep-hours'));

        $this->info("Pruned {$deleted} used/expired member login token(s).");

        return self::SUCCESS;
    }
}
