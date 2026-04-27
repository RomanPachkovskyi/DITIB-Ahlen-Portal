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
                        TextInput::make('full_name')
                            ->label('Vor- und Nachname')
                            ->required(),
                        DatePicker::make('birth_date')
                            ->label('Geburtsdatum')
                            ->required(),
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
                            ->label('Telefonnummer')
                            ->tel()
                            ->required(),
                    ]),

                Section::make('Bankverbindung (SEPA)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('jahresbeitrag')
                            ->label('Jahresbeitrag (€)')
                            ->numeric()
                            ->default(36.00)
                            ->prefix('€')
                            ->required(),
                        TextInput::make('kontoinhaber')
                            ->label('Kontoinhaber')
                            ->required(),
                        TextInput::make('iban')
                            ->label('IBAN')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('bic')
                            ->label('BIC'),
                        TextInput::make('kreditinstitut')
                            ->label('Kreditinstitut'),
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
