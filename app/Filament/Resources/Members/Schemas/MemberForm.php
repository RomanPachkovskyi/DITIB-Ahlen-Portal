<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Models\Member;
use App\Support\Iban;
use App\Support\Instagram;
use App\Support\MemberStatus;
use App\Support\PhoneNumber;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

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
                                    ->validationMessages(['regex' => 'Der Name darf тільки літери та пробіли.'])
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

                Section::make('Status & Verwaltung')
                    ->extraAttributes(['style' => 'grid-row: span 2'])
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
                            ->required(),
                        DateTimePicker::make('zustimmung_at')
                            ->label('Zustimmung am')
                            ->disabledOn('edit'),
                        Toggle::make('sepa_zustimmung')
                            ->label('SEPA-Lastschriftmandat')
                            ->disabledOn('edit'),
                        Toggle::make('dsgvo_zustimmung')
                            ->label('Datenschutzerklärung')
                            ->disabledOn('edit'),
                        Toggle::make('profile_photo_zustimmung')
                            ->label('Foto-Einwilligung')
                            ->disabledOn('edit'),
                        DateTimePicker::make('profile_photo_zustimmung_at')
                            ->label('Foto-Einwilligung am')
                            ->disabledOn('edit'),
                        Textarea::make('admin_notiz')
                            ->label('Interne Notiz'),
                    ]),

                Section::make('Beitrag & Bankverbindung')
                    ->columns(2)
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
                            ->live(onBlur: true),
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
                            ->live(onBlur: true)
                            ->columnSpanFull(),
                        TextInput::make('bic')
                            ->label('BIC')
                            ->visible(fn ($get) => $get('zahlungsart') === 'lastschrift')
                            ->live(onBlur: true),
                        TextInput::make('kreditinstitut')
                            ->label('Kreditinstitut')
                            ->visible(fn ($get) => $get('zahlungsart') === 'lastschrift')
                            ->regex('/^[\pL\s\-]+$/u')
                            ->live(onBlur: true),
                    ]),

            ]);
    }
}
