<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Persönliche Daten')
                    ->columns(2)
                    ->schema([
                        TextInput::make('member_number')
                            ->label('Mitgliedsnummer')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('full_name')
                            ->label('Vor- und Nachname')
                            ->required(),
                        DatePicker::make('birth_date')
                            ->label('Geburtsdatum')
                            ->required(),
                        TextInput::make('birth_place')
                            ->label('Geburtsort'),
                        TextInput::make('staatsangehoerigkeit')
                            ->label('Staatsangehörigkeit'),
                        TextInput::make('familienangehoerige')
                            ->label('Anzahl der Familienangehörigen')
                            ->numeric()
                            ->default(1),
                        TextInput::make('beruf')
                            ->label('Beruf'),
                        TextInput::make('heimatstadt')
                            ->label('Heimatstadt'),
                        TextInput::make('street')
                            ->label('Straße und Hausnummer')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('postal_code')
                            ->label('Postleitzahl')
                            ->required(),
                        TextInput::make('city')
                            ->label('Ort')
                            ->required(),
                        TextInput::make('state')
                            ->label('Bundesland')
                            ->required(),
                        TextInput::make('email')
                            ->label('E-Mail')
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->required(),
                        Toggle::make('cenaze_fonu')
                            ->label('Mitglied des Bestattungsinstituts (Cenaze Fonu)'),
                        TextInput::make('cenaze_fonu_nr')
                            ->label('Cenaze Fonu Nr.')
                            ->visible(fn ($get) => $get('cenaze_fonu')),
                        Toggle::make('gemeinderegister')
                            ->label('Im Gemeinderegister eingetragen'),
                    ]),

                Section::make('Beitrag & Bankverbindung')
                    ->columns(2)
                    ->schema([
                        TextInput::make('monatsbeitrag')
                            ->label('Monatlicher Mitgliedsbeitrag (€)')
                            ->numeric()
                            ->default(25.00)
                            ->minValue(25)
                            ->prefix('€')
                            ->required(),
                        Select::make('zahlungsart')
                            ->label('Zahlungsweise')
                            ->options([
                                'barzahlung'  => 'Barzahlung',
                                'lastschrift' => 'Lastschrift',
                                'dauerauftrag' => 'Dauerauftrag',
                            ])
                            ->default('barzahlung')
                            ->required()
                            ->live(),
                        TextInput::make('kontoinhaber')
                            ->label('Kontoinhaber')
                            ->visible(fn ($get) => in_array($get('zahlungsart'), ['lastschrift', 'dauerauftrag'])),
                        TextInput::make('iban')
                            ->label('IBAN')
                            ->visible(fn ($get) => in_array($get('zahlungsart'), ['lastschrift', 'dauerauftrag']))
                            ->columnSpanFull(),
                        TextInput::make('bic')
                            ->label('BIC')
                            ->visible(fn ($get) => in_array($get('zahlungsart'), ['lastschrift', 'dauerauftrag'])),
                        TextInput::make('kreditinstitut')
                            ->label('Kreditinstitut')
                            ->visible(fn ($get) => in_array($get('zahlungsart'), ['lastschrift', 'dauerauftrag'])),
                    ]),

                Section::make('Status & Verwaltung')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending'  => 'Ausstehend',
                                'active'   => 'Aktiv',
                                'inactive' => 'Inaktiv',
                            ])
                            ->default('pending')
                            ->required(),
                        DateTimePicker::make('zustimmung_at')
                            ->label('Zustimmung am'),
                        Toggle::make('sepa_zustimmung')
                            ->label('SEPA-Lastschriftmandat'),
                        Toggle::make('dsgvo_zustimmung')
                            ->label('Datenschutzerklärung'),
                        Textarea::make('admin_notiz')
                            ->label('Interne Notiz')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
