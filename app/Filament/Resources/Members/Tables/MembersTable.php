<?php

namespace App\Filament\Resources\Members\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

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
                    ViewAction::make()->label('Anzeigen'),
                    EditAction::make()->label('Bearbeiten'),
                    \Filament\Actions\DeleteAction::make()->label('Löschen'),
                    RestoreAction::make()->label('Wiederherstellen'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
