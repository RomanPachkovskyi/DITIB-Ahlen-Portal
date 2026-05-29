<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    /**
     * @return array<string>
     */
    public function getRenderHookScopes(): array
    {
        return [
            static::class,
            BaseLogin::class,
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return 'Admin-Anmeldung';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Admin-Anmeldung';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Zugang zur Mitgliederverwaltung';
    }
}
