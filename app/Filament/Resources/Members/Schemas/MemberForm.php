<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use App\Support\Iban;
use App\Support\Instagram;
use App\Support\MemberStatus;
use App\Support\PhoneNumber;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        // Admin entry point — unchanged behaviour.
        return self::build($schema, MemberFormContext::AdminEdit);
    }

    /**
     * Whether the bank-related form fields differ from the stored record.
     * Mirrors the server-side rule in EditMemberAccount so the SEPA re-consent
     * checkbox appears exactly when consent will be required.
     */
    protected static function bankDataChanged($get, Member $record): bool
    {
        return $get('zahlungsart') !== $record->zahlungsart
            || Iban::normalize($get('iban')) !== (string) $record->iban
            || (string) $get('bic') !== (string) $record->bic
            || (string) $get('kontoinhaber') !== (string) $record->kontoinhaber
            || (string) $get('kreditinstitut') !== (string) $record->kreditinstitut;
    }

    /**
     * Shared schema builder for both admin and member (/konto) panels.
     *
     * The card is identical across contexts except for a few member deltas:
     * `admin_notiz` is hidden, and `status`/`email` are read-only. The server-
     * side write boundary for member edits stays in MemberFormContext::
     * onlyMemberEditable() (the EditMemberAccount save flow), not in field
     * visibility — hidden/disabled fields are UX, not security.
     */
    public static function build(Schema $schema, MemberFormContext $context): Schema
    {
        $isMember = $context->isMember();

        return $schema
            ->components([

                Group::make([
                    Section::make('Persönliche Daten')
                        ->schema([
                            View::make('filament.members.profile-photo-preview')
                                ->viewData(fn (?Member $record): array => [
                                    'member' => $record,
                                ])
                                ->visible(fn (?Member $record): bool => $record?->profile_photo_path !== null),
                            Placeholder::make('member_number_display')
                                ->hiddenLabel()
                                ->content(fn (?Member $record) => $record
                                    ? new HtmlString('<span style="white-space: nowrap;">Mitgliedsnummer: '.e($record->member_number).'</span>')
                                    : '')
                                ->extraAttributes([
                                    'class' => 'text-left text-gray-500 text-sm font-semibold tracking-wider',
                                    'style' => 'white-space: nowrap;',
                                ])
                                ->visible(fn ($record) => $record !== null),
                            Grid::make(['default' => 1, 'sm' => 2])
                                ->schema([
                                    TextInput::make('full_name')
                                        ->label('Vor- und Nachname')
                                        ->required()
                                        ->maxLength(255)
                                        ->regex('/^[\pL\s\-]+$/u')
                                        ->columnSpan([
                                            'default' => 'full',
                                            'sm' => 1,
                                        ])
                                        ->validationMessages(['regex' => 'Der Name darf nur Buchstaben, Leerzeichen und Bindestriche enthalten.'])
                                        ->live(onBlur: true),
                                    Select::make('anrede')
                                        ->label('Anrede')
                                        ->options([
                                            'Frau' => 'Frau',
                                            'Herr' => 'Herr',
                                        ])
                                        ->required()
                                        ->columnSpan(1),
                                    DatePicker::make('birth_date')
                                        ->label('Geburtsdatum')
                                        ->required()
                                        ->live(onBlur: true),
                                    TextInput::make('birth_place')
                                        ->label('Geburtsort')
                                        ->regex('/^[\pL\s\-]+$/u')
                                        ->live(onBlur: true),
                                    TextInput::make('staatsangehoerigkeit')
                                        ->label('Staatsangehörigkeit')
                                        ->regex('/^[\pL\s\-]+$/u')
                                        ->live(onBlur: true),
                                    TextInput::make('familienangehoerige')
                                        ->label('Familienangehörige (Anzahl)')
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->live(onBlur: true),
                                    TextInput::make('beruf')
                                        ->label('Beruf')
                                        ->regex('/^[\pL\s\-]+$/u')
                                        ->live(onBlur: true),
                                    TextInput::make('heimatstadt')
                                        ->label('Heimatstadt')
                                        ->regex('/^[\pL\s\-]+$/u')
                                        ->live(onBlur: true),
                                    TextInput::make('street')
                                        ->label('Straße und Hausnummer')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->columnSpanFull(),
                                    TextInput::make('postal_code')
                                        ->label('Postleitzahl')
                                        ->regex('/^[0-9]{5}$/')
                                        ->required()
                                        ->maxLength(5)
                                        ->extraInputAttributes([
                                            'inputmode' => 'numeric',
                                            'maxlength' => '5',
                                            'oninput' => "this.value = this.value.replace(/[^0-9]/g, '').slice(0,5)",
                                        ])
                                        ->live(onBlur: true),
                                    TextInput::make('city')
                                        ->label('Ort')
                                        ->regex('/^[\pL\s\-]+$/u')
                                        ->required()
                                        ->live(onBlur: true),
                                    TextInput::make('state')
                                        ->label('Bundesland')
                                        ->regex('/^[\pL\s\-]+$/u')
                                        ->required()
                                        ->live(onBlur: true),
                                    TextInput::make('phone')
                                        ->label('Telefon')
                                        ->tel()
                                        ->required()
                                        ->maxLength(50)
                                        ->formatStateUsing(fn (?string $state): string => PhoneNumber::format($state))
                                        ->dehydrateStateUsing(fn (?string $state): string => PhoneNumber::normalize($state))
                                        ->rule(fn () => function (string $attribute, ?string $value, \Closure $fail): void {
                                            if (! PhoneNumber::isValid($value)) {
                                                $fail(PhoneNumber::validationMessage());
                                            }
                                        })
                                        ->live(onBlur: true),
                                    TextInput::make('instagram')
                                        ->label('Instagram')
                                        ->placeholder('@benutzername')
                                        ->maxLength(255)
                                        ->formatStateUsing(fn (?string $state): string => Instagram::display($state))
                                        ->dehydrateStateUsing(fn (?string $state): ?string => Instagram::normalize($state))
                                        ->rule(fn () => function (string $attribute, ?string $value, \Closure $fail): void {
                                            if (! Instagram::isValid($value)) {
                                                $fail('Bitte geben Sie einen Instagram-Namen oder Instagram-Link ein.');
                                            }
                                        })
                                        ->live(onBlur: true),
                                    TextInput::make('email')
                                        ->label('E-Mail')
                                        ->email()
                                        ->required()
                                        // Email is the /konto access key, read-only for members in v1.
                                        ->disabled($isMember)
                                        ->live(onBlur: true)
                                        ->columnSpanFull(),
                                    Toggle::make('cenaze_fonu')
                                        ->label('Mitglied des Bestattungsinstituts (Cenaze Fonu)')
                                        ->live()
                                        ->columnSpanFull(),
                                    TextInput::make('cenaze_fonu_nr')
                                        ->label('Cenaze Fonu Nr.')
                                        ->visible(fn ($get) => $get('cenaze_fonu'))
                                        ->live(onBlur: true),
                                    Toggle::make('gemeinderegister')
                                        ->label('Im Gemeinderegister eingetragen')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Section::make('Beitrag & Bankverbindung')
                        ->columns(2)
                        // Beitrag/Bankverbindung is member-managed via /konto; for admin
                        // the whole block is read-only (security + single source of truth).
                        ->disabled(! $isMember)
                        ->schema([
                            TextInput::make('monatsbeitrag')
                                ->label('Monatlicher Mitgliedsbeitrag (€)')
                                ->numeric()
                                ->default(10.00)
                                ->minValue(10)
                                ->prefix('€')
                                ->required()
                                ->extraInputAttributes([
                                    'min' => '10',
                                    'x-on:input' => "if(parseFloat(this.value) < 10 && this.value !== '') this.classList.add('border-red-400'); else this.classList.remove('border-red-400');",
                                ])
                                ->live(onBlur: true),
                            Select::make('zahlungsart')
                                ->label('Zahlungsweise')
                                ->options([
                                    'barzahlung' => 'Barzahlung',
                                    'lastschrift' => 'Lastschrift',
                                    'dauerauftrag' => 'Dauerauftrag',
                                ])
                                ->default('barzahlung')
                                ->required()
                                ->live(),
                            TextInput::make('kontoinhaber')
                                ->label('Kontoinhaber')
                                ->visible(fn ($get) => $get('zahlungsart') === 'lastschrift')
                                ->required(fn ($get) => $get('zahlungsart') === 'lastschrift')
                                ->regex('/^[\pL\s\-]+$/u')
                                // live() (not onBlur): bank changes must sync immediately so
                                // the SEPA re-consent checkbox appears and the change reaches
                                // the server even if the user clicks Save without blurring.
                                ->live(),
                            TextInput::make('iban')
                                ->label('IBAN')
                                ->visible(fn ($get) => $get('zahlungsart') === 'lastschrift')
                                ->required(fn ($get) => $get('zahlungsart') === 'lastschrift')
                                ->formatStateUsing(fn (?string $state): string => Iban::format($state))
                                ->dehydrateStateUsing(fn (?string $state): string => Iban::normalize($state))
                                ->rule(fn () => function (string $attribute, ?string $value, \Closure $fail): void {
                                    if (! Iban::isValidStructure($value)) {
                                        $fail('Ungültige IBAN.');
                                    }
                                })
                                ->live()
                                ->columnSpanFull(),
                            TextInput::make('bic')
                                ->label('BIC')
                                ->visible(fn ($get) => $get('zahlungsart') === 'lastschrift')
                                ->live(),
                            TextInput::make('kreditinstitut')
                                ->label('Kreditinstitut')
                                ->visible(fn ($get) => $get('zahlungsart') === 'lastschrift')
                                ->regex('/^[\pL\s\-]+$/u')
                                ->live(),
                            // Member self-edit only: a fresh SEPA mandate must be
                            // confirmed whenever bank data changes (Roman's
                            // decision 5). Not a Member column — EditMemberAccount
                            // reads it and sets sepa_zustimmung + sepa_zustimmung_at.
                            Checkbox::make('sepa_reconsent')
                                ->label('Ich bestätige das SEPA-Lastschriftmandat für diese Bankverbindung.')
                                ->dehydrated(true)
                                // Member edit only, and only once the bank data actually
                                // differs from the stored record — this mirrors the
                                // server-side "bank changed" rule, so the checkbox shows
                                // exactly when consent is required (no nagging otherwise).
                                ->visible(fn ($get, ?Member $record, string $operation): bool => $isMember
                                    && $operation === 'edit'
                                    && $get('zahlungsart') === 'lastschrift'
                                    && $record instanceof Member
                                    && self::bankDataChanged($get, $record))
                                ->columnSpanFull(),
                        ]),
                ]),

                Group::make([
                    Section::make('Status & Verwaltung')
                        ->schema([
                            ToggleButtons::make('status')
                                ->label('Status')
                                ->options(MemberStatus::labels())
                                ->icons(MemberStatus::icons())
                                ->colors(MemberStatus::colors())
                                ->disableOptionWhen(fn (string $value, ?Member $record = null): bool => in_array($value, MemberStatus::openStatuses(), true)
                                    && $record?->status !== $value)
                                ->inline()
                                ->default('pending')
                                ->required()
                                // Members see their status read-only; only admin changes it.
                                ->disabled($isMember),
                            DateTimePicker::make('zustimmung_at')
                                ->label('Zustimmung am')
                                ->disabledOn('edit'),
                            Toggle::make('sepa_zustimmung')
                                ->label('SEPA-Lastschriftmandat')
                                ->disabledOn('edit'),
                            DateTimePicker::make('sepa_zustimmung_at')
                                ->label('SEPA-Zustimmung am')
                                ->visible(fn ($get): bool => (bool) $get('sepa_zustimmung'))
                                ->disabledOn('edit'),
                            Toggle::make('dsgvo_zustimmung')
                                ->label('Datenschutzerklärung')
                                ->disabledOn('edit'),
                            Toggle::make('profile_photo_zustimmung')
                                ->label('Foto-Einwilligung')
                                // Photo consent is meaningless without a stored photo;
                                // hide it (and its timestamp) when none exists — both panels.
                                ->visible(fn (?Member $record): bool => $record?->profile_photo_path !== null)
                                ->disabledOn('edit'),
                            DateTimePicker::make('profile_photo_zustimmung_at')
                                ->label('Foto-Einwilligung am')
                                ->visible(fn (?Member $record): bool => $record?->profile_photo_path !== null)
                                ->disabledOn('edit'),
                            Textarea::make('admin_notiz')
                                ->label('Interne Notiz')
                                // Internal note is admin-only; never shown to members.
                                ->visible(! $isMember),
                        ]),
                    Placeholder::make('audit_logs_link')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'ditib-audit-log-link'])
                        ->content(function (?Member $record) use ($isMember): HtmlString {
                            if (! $record) {
                                return new HtmlString('');
                            }

                            $url = $isMember
                                ? MemberAccountResource::getUrl('logs', ['record' => $record], panel: 'member')
                                : MemberResource::getUrl('logs', ['record' => $record], panel: 'admin');

                            return new HtmlString('<a href="'.e($url).'" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400" style="display: block; width: 100%; text-align: center;">Historie dieses Eintrags anzeigen</a>');
                        })
                        ->visible(fn (?Member $record): bool => $record !== null),
                ]),

            ]);
    }
}
