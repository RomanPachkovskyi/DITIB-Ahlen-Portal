<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;

/**
 * Extends Filament's Authenticate to redirect non-admin users to the admin
 * login page (with the intended URL preserved) instead of returning a 403.
 *
 * Root cause: admin and member panels share the same web guard. When a member
 * is logged in and clicks the "open in admin" link from an email, Filament sees
 * an authenticated user whose canAccessPanel('admin') returns false and calls
 * abort(403). This middleware converts that case into a redirect-to-login so
 * the recipient can authenticate as an admin and land on the record directly.
 */
class AdminPanelAuthenticate extends Authenticate
{
    /**
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return; /** @phpstan-ignore-line */
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentOrDefaultPanel();

        $canAccess = $user instanceof FilamentUser
            ? $user->canAccessPanel($panel)
            : config('app.env') === 'local';

        if (! $canAccess) {
            // Redirect to admin login with the intended URL preserved so the
            // admin can authenticate and land directly on the requested record.
            $this->unauthenticated($request, $guards);

            return; /** @phpstan-ignore-line */
        }
    }
}
