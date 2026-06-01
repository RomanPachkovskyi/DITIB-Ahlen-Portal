<?php

namespace App\Filament\Member\Pages\Auth;

use App\Mail\MemberLoginLinkMail;
use App\Services\MemberMagicLoginService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Pages\Login;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class RequestLoginLink extends Login
{
    public bool $linkRequestSent = false;

    /**
     * @return array<string>
     */
    public function getRenderHookScopes(): array
    {
        return [
            static::class,
            Login::class,
        ];
    }

    public function authenticate(): null
    {
        try {
            $this->rateLimit(3);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $email = (string) ($data['email'] ?? '');

        $magicLogin = app(MemberMagicLoginService::class);
        $loginLink = $magicLogin->createForEmail($email, request()->ip(), request()->userAgent());

        if ($loginLink !== null) {
            try {
                Mail::to($magicLogin->normalizeEmail($email))->send(new MemberLoginLinkMail($loginLink['url']));
            } catch (\Throwable $exception) {
                Log::error('Failed to send member login link mail.', [
                    'email' => $magicLogin->normalizeEmail($email),
                    'exception' => $exception,
                ]);
            }
        }

        $this->linkRequestSent = true;
        $this->form->fill(['email' => $magicLogin->normalizeEmail($email)]);

        Notification::make()
            ->title('Wenn diese E-Mail-Adresse bei uns registriert ist, senden wir Ihnen einen Zugangslink.')
            ->success()
            ->send();

        return null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getSentMessageComponent(),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE),
                $this->getFormContentComponent(),
                $this->getRegistrationLinkComponent(),
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER),
            ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('E-Mail-Adresse')
            ->email()
            ->required()
            ->autocomplete('email')
            ->autofocus();
    }

    protected function getSentMessageComponent(): Component
    {
        return Placeholder::make('sent_message')
            ->hiddenLabel()
            ->content(new HtmlString(
                '<div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">'
                .'Wenn diese E-Mail-Adresse bei uns registriert ist, senden wir Ihnen einen Zugangslink. '
                .'Der Link ist 60 Minuten gültig und kann nur einmal verwendet werden. '
                .'Falls Sie noch keinen Antrag gestellt haben, können Sie sich über die öffentliche Registrierung anmelden.'
                .'</div>'
            ))
            ->visible(fn (): bool => $this->linkRequestSent);
    }

    protected function getRegistrationLinkComponent(): Component
    {
        return Placeholder::make('registration_link')
            ->hiddenLabel()
            ->content(new HtmlString(
                '<div class="ditib-konto-register-link">'
                .'Noch kein Mitgliedskonto? '
                .'<a href="https://mitglied.ditib-ahlen-projekte.de/" target="_blank" rel="noopener noreferrer">Jetzt registrieren</a>'
                .'</div>'
            ));
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Zugangslink senden')
            ->submit('authenticate');
    }

    public function getTitle(): string | Htmlable
    {
        return 'Mitgliedskonto öffnen';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Mitgliedskonto öffnen';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Geben Sie die in Ihrer Mitgliedschaft hinterlegte E-Mail-Adresse ein. '
            .'Ist diese bei uns registriert, senden wir einen einmaligen Zugangslink an genau diese Adresse.';
    }
}
