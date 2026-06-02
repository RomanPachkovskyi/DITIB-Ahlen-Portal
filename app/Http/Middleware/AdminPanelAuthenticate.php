<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Extends Filament's Authenticate to redirect non-admin users to the admin
 * login page (with the intended URL preserved) instead of returning a 403.
 *
 * Root cause: admin and member panels share the same web guard. When a member
 * is logged in and clicks the "open in admin" link from an email, Filament sees
 * an authenticated user whose canAccessPanel('admin') returns false and calls
 * abort(403). This middleware converts that case into a redirect-to-login.
 *
 * We must log out the session before redirecting because Filament's
 * Login::mount() calls redirect()->intended() as soon as auth()->check() is
 * true — which would send the user back here and create an infinite loop.
 */
class AdminPanelAuthenticate extends Authenticate
{
    public function handle($request, Closure $next, ...$guards): mixed
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            // Guest: standard Filament behaviour — redirect to login.
            $this->unauthenticated($request, $guards);

            return null; /** @phpstan-ignore-line (unauthenticated throws) */
        }

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentOrDefaultPanel();

        $canAccess = $user instanceof FilamentUser
            ? $user->canAccessPanel($panel)
            : config('app.env') === 'local';

        if (! $canAccess) {
            // The user is authenticated but not an admin. We log them out so
            // the login page shows its form rather than calling
            // redirect()->intended() (which would loop back here indefinitely).
            // After session reset we store url.intended so the admin lands on
            // the record after a successful login.
            $intendedUrl = $request->url();

            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->put('url.intended', $intendedUrl);

            return redirect()->to(Filament::getLoginUrl());
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        return $next($request);
    }
}
