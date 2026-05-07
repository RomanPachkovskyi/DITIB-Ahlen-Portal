<?php

namespace App\Filament\Resources\Members\Tables;

use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use App\Support\PhoneNumber;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_number')
                    ->label('Nr.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->grow(false),

                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'pending'  => 'heroicon-m-sparkles',
                        'active'   => 'heroicon-m-check-circle',
                        'inactive' => 'heroicon-m-x-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'active'   => 'success',
                        'inactive' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'  => 'Ausstehend',
                        'active'   => 'Aktiv',
                        'inactive' => 'Inaktiv',
                    })
                    ->sortable()
                    ->grow(false),

                TextColumn::make('deleted_at')
                    ->label('Gelöscht am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('city')
                    ->label('Ort')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('monatsbeitrag')
                    ->label('Beitrag/Mo.')
                    ->money('EUR')
                    ->sortable()
                    ->grow(false)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Eingegangen am')
                    ->date('d.m.Y')
                    ->sortable()
                    ->grow(false)
                    ->toggleable(),

                // Додаткові колонки — приховані за замовчуванням
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->formatStateUsing(fn (?string $state): string => PhoneNumber::format($state))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('zahlungsart')
                    ->label('Zahlungsart')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'barzahlung'   => 'Barzahlung',
                        'lastschrift'  => 'Lastschrift',
                        'dauerauftrag' => 'Dauerauftrag',
                        default        => $state,
                    })
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('staatsangehoerigkeit')
                    ->label('Staatsangehörigkeit')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('birth_date')
                    ->label('Geburtsdatum')
                    ->date('d.m.Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Member $record): string => MemberResource::getUrl('view', ['record' => $record]))
            ->recordAction(null)
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Ausstehend',
                        'active'   => 'Aktiv',
                        'inactive' => 'Inaktiv',
                    ]),
                SelectFilter::make('zahlungsart')
                    ->label('Zahlungsart')
                    ->options([
                        'barzahlung'   => 'Barzahlung',
                        'lastschrift'  => 'Lastschrift',
                        'dauerauftrag' => 'Dauerauftrag',
                    ]),
                TrashedFilter::make()
                    ->label('Gelöschte Einträge'),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    EditAction::make()
                        ->label('Bearbeiten')
                        ->icon('heroicon-m-pencil-square')
                        ->color('gray'),
                    \Filament\Actions\ActionGroup::make(self::statusRecordActions())
                        ->dropdown(false),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make(self::statusBulkActions()),
            ]);
    }

    /**
     * @return array<Action>
     */
    private static function statusRecordActions(): array
    {
        return [
            self::statusRecordAction('pending', 'Als Ausstehend markieren', 'heroicon-m-sparkles', 'warning'),
            self::statusRecordAction('active', 'Als Aktiv markieren', 'heroicon-m-check-circle', 'success'),
            self::statusRecordAction('inactive', 'Als Inaktiv markieren', 'heroicon-m-x-circle', 'danger'),
        ];
    }

    private static function statusRecordAction(string $status, string $label, string $icon, string $color): Action
    {
        return Action::make('set_status_' . $status)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->extraAttributes(self::statusActionAttributes($status))
            ->hidden(fn (Member $record): bool => $record->status === $status)
            ->action(fn (Member $record): bool => $record->update(['status' => $status]))
            ->successNotificationTitle('Status aktualisiert');
    }

    /**
     * @return array<BulkAction>
     */
    private static function statusBulkActions(): array
    {
        return [
            self::statusBulkAction('pending', 'Auf Ausstehend setzen', 'heroicon-m-sparkles', 'warning'),
            self::statusBulkAction('active', 'Auf Aktiv setzen', 'heroicon-m-check-circle', 'success'),
            self::statusBulkAction('inactive', 'Auf Inaktiv setzen', 'heroicon-m-x-circle', 'danger'),
        ];
    }

    private static function statusBulkAction(string $status, string $label, string $icon, string $color): BulkAction
    {
        return BulkAction::make('bulk_set_status_' . $status)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->extraAttributes(self::statusActionAttributes($status))
            ->requiresConfirmation()
            ->action(fn (EloquentCollection $records) => $records->each(fn (Member $record): bool => $record->update(['status' => $status])))
            ->deselectRecordsAfterCompletion()
            ->successNotificationTitle('Status für ausgewählte Mitglieder aktualisiert');
    }

    /**
     * @return array<string, string>
     */
    private static function statusActionAttributes(string $status): array
    {
        $color = match ($status) {
            'active' => 'oklch(0.527 0.154 150.069)',
            'pending' => 'oklch(0.555 0.163 48.998)',
            default => null,
        };

        if (! $color) {
            return [];
        }

        return [
            'class' => "fi-text-color-700 ditib-status-action-{$status}",
            'style' => "--color-700: {$color}; --text: {$color}; --hover-text: {$color}; color: {$color};",
        ];
    }
}
