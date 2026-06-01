<?php

namespace App\Filament\Resources\Members\Tables;

use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use App\Services\MemberAuditLogger;
use App\Support\Instagram;
use App\Support\MemberStatus;
use App\Support\PhoneNumber;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
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
                    ->icon(fn (string $state): string => MemberStatus::icon($state))
                    ->color(fn (string $state): string => MemberStatus::color($state))
                    ->formatStateUsing(fn (string $state): string => MemberStatus::label($state))
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

                TextColumn::make('instagram')
                    ->label('Instagram')
                    ->formatStateUsing(fn (?string $state): string => Instagram::display($state))
                    ->url(fn (?string $state): ?string => Instagram::url($state))
                    ->openUrlInNewTab()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('zahlungsart')
                    ->label('Zahlungsart')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'barzahlung' => 'Barzahlung',
                        'lastschrift' => 'Lastschrift',
                        'dauerauftrag' => 'Dauerauftrag',
                        default => $state,
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
            ->defaultPaginationPageOption(25)
            ->recordUrl(fn (Member $record): string => MemberResource::getUrl('view', ['record' => $record]))
            ->recordAction(null)
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(MemberStatus::labels()),
                SelectFilter::make('zahlungsart')
                    ->label('Zahlungsart')
                    ->options([
                        'barzahlung' => 'Barzahlung',
                        'lastschrift' => 'Lastschrift',
                        'dauerauftrag' => 'Dauerauftrag',
                    ]),
                TrashedFilter::make()
                    ->label('Gelöschte Einträge'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Bearbeiten')
                        ->icon('heroicon-m-pencil-square')
                        ->color('gray'),
                    ActionGroup::make(self::statusRecordActions())
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
        return collect(MemberStatus::adminActionLabels())
            ->map(fn (string $label, string $status): Action => self::statusRecordAction(
                $status,
                $label,
                MemberStatus::icon($status),
                MemberStatus::color($status),
            ))
            ->all();
    }

    private static function statusRecordAction(string $status, string $label, string $icon, string $color): Action
    {
        return Action::make('set_status_'.$status)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->extraAttributes(self::statusActionAttributes($status))
            ->hidden(fn (Member $record): bool => $record->status === $status)
            ->action(function (Member $record) use ($status): bool {
                $oldStatus = $record->status;
                $updated = $record->update(['status' => $status]);

                if ($updated) {
                    app(MemberAuditLogger::class)->statusChanged($record, $oldStatus, $status, 'admin');
                }

                return $updated;
            })
            ->successNotificationTitle('Status aktualisiert');
    }

    /**
     * @return array<BulkAction>
     */
    private static function statusBulkActions(): array
    {
        return collect(MemberStatus::adminBulkActionLabels())
            ->map(fn (string $label, string $status): BulkAction => self::statusBulkAction(
                $status,
                $label,
                MemberStatus::icon($status),
                MemberStatus::color($status),
            ))
            ->all();
    }

    private static function statusBulkAction(string $status, string $label, string $icon, string $color): BulkAction
    {
        return BulkAction::make('bulk_set_status_'.$status)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->extraAttributes(self::statusActionAttributes($status))
            ->requiresConfirmation()
            ->action(function (EloquentCollection $records) use ($status): void {
                $records->each(function (Member $record) use ($status): void {
                    $oldStatus = $record->status;

                    if ($record->update(['status' => $status])) {
                        app(MemberAuditLogger::class)->statusChanged($record, $oldStatus, $status, 'admin');
                    }
                });
            })
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
            'pending', 'processing' => 'oklch(0.555 0.163 48.998)',
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
