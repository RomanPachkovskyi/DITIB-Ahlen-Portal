<?php

namespace App\Filament\Resources\EmailLogs;

use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Models\EmailLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmailLogResource extends Resource
{
    protected static ?string $model = EmailLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'E-Mail Logs';

    protected static ?string $modelLabel = 'E-Mail Log';

    protected static ?string $pluralModelLabel = 'E-Mail Logs';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('member.full_name')
                    ->label('Mitglied')
                    ->searchable()
                    ->default('—')
                    ->url(fn (EmailLog $record): ?string => $record->member
                        ? \App\Filament\Resources\Members\MemberResource::getUrl('view', ['record' => $record->member])
                        : null
                    ),

                TextColumn::make('event')
                    ->label('E-Mail Typ')
                    ->formatStateUsing(fn (EmailLog $record): string => $record->eventLabel())
                    ->searchable(),

                TextColumn::make('recipient_type')
                    ->label('Empfänger')
                    ->formatStateUsing(fn (EmailLog $record): string => $record->recipientTypeLabel()),

                TextColumn::make('recipient_email')
                    ->label('E-Mail Adresse')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent'   => 'success',
                        'failed' => 'danger',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sent'   => 'Gesendet',
                        'failed' => 'Fehler',
                        default  => $state,
                    }),

                TextColumn::make('error_message')
                    ->label('Fehler')
                    ->default('—')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'sent'   => 'Gesendet',
                        'failed' => 'Fehler',
                    ]),

                SelectFilter::make('recipient_type')
                    ->label('Empfänger')
                    ->options([
                        'member' => 'Mitglied',
                        'admin'  => 'Admin',
                    ]),

                SelectFilter::make('event')
                    ->label('E-Mail Typ')
                    ->options([
                        'registration_confirmation' => 'Registrierungsbestätigung',
                        'admin_new_member'          => 'Neues Mitglied (Admin)',
                        'member_approved'           => 'Mitgliedschaft bestätigt',
                        'member_deleted'            => 'Mitgliedschaft gelöscht',
                        'admin_member_deleted'      => 'Mitglied gelöscht (Admin)',
                        'admin_member_updated'      => 'Datenänderung (Admin)',
                        'login_link'                => 'Login-Link',
                    ]),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailLogs::route('/'),
        ];
    }
}
