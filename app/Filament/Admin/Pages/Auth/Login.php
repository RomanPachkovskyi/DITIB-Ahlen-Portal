<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

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

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE),
                $this->getFormContentComponent(),
                $this->getMemberLinkComponent(),
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER),
            ]);
    }

    protected function getMemberLinkComponent(): Component
    {
        // For visitors who landed on the admin login by mistake: point them to
        // the member account login instead.
        return Placeholder::make('member_link')
            ->hiddenLabel()
            ->content(new HtmlString(
                '<div class="ditib-konto-register-link">'
                .'Sind Sie Mitglied? '
                .'<a href="'.e(url('/konto')).'">Zum Mitgliedskonto →</a>'
                .'</div>'
            ));
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
