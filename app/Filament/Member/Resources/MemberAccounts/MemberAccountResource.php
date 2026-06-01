<?php

namespace App\Filament\Member\Resources\MemberAccounts;

use App\Filament\Member\Resources\MemberAccounts\Pages\EditMemberAccount;
use App\Filament\Member\Resources\MemberAccounts\Pages\ListMemberAccounts;
use App\Filament\Member\Resources\MemberAccounts\Pages\ViewMemberAccount;
use App\Filament\Member\Resources\MemberAccounts\Pages\ViewMemberAccountLogs;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Members\Schemas\MemberFormContext;
use App\Models\Member;
use App\Support\MemberStatus;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MemberAccountResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $slug = 'mitgliedschaften';

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'Meine Mitgliedschaften';

    protected static ?string $modelLabel = 'Mitgliedschaft';

    protected static ?string $pluralModelLabel = 'Meine Mitgliedschaften';

    public static function getEloquentQuery(): Builder
    {
        $email = Filament::auth()->user()?->email;

        return parent::getEloquentQuery()
            ->whereRaw('lower(email) = ?', [mb_strtolower((string) $email)]);
    }

    public static function canViewAny(): bool
    {
        return Filament::auth()->check();
    }

    public static function canView(Model $record): bool
    {
        // Inactive records are listed (dimmed) but cannot be opened.
        return static::belongsToCurrentUser($record) && ! static::isInactive($record);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        // Members edit only their own, non-inactive records.
        return static::belongsToCurrentUser($record) && ! static::isInactive($record);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    protected static function belongsToCurrentUser(Model $record): bool
    {
        $email = Filament::auth()->user()?->email;

        return $email !== null
            && $record instanceof Member
            && $record->email !== null
            && strcasecmp($record->email, $email) === 0;
    }

    protected static function isInactive(Model $record): bool
    {
        return $record instanceof Member && $record->status === MemberStatus::INACTIVE;
    }

    public static function form(Schema $schema): Schema
    {
        // Same card as admin, minus admin-only fields (member context).
        return MemberForm::build($schema, MemberFormContext::MemberView);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_number')
                    ->label('Nr.')
                    ->sortable()
                    ->copyable()
                    ->grow(false),
                TextColumn::make('full_name')
                    ->label('Name')
                    ->sortable()
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn (string $state): string => MemberStatus::icon($state))
                    ->color(fn (string $state): string => MemberStatus::color($state))
                    ->formatStateUsing(fn (string $state): string => MemberStatus::label($state))
                    ->grow(false),
                TextColumn::make('email')
                    ->label('E-Mail')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('city')
                    ->label('Ort')
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
            ])
            ->defaultSort('member_number')
            // Inactive rows are shown dimmed and are not clickable.
            ->recordClasses(fn (Member $record): ?string => static::isInactive($record) ? 'ditib-inactive-row' : null)
            ->recordUrl(fn (Member $record): ?string => static::isInactive($record)
                ? null
                : static::getUrl('view', ['record' => $record]))
            // Active rows open the detail view; inactive rows open the info modal
            // on a click anywhere in the row (mobile-friendly).
            ->recordAction(fn (Member $record): ?string => static::isInactive($record) ? 'inactiveInfo' : null)
            ->recordActions([
                ViewAction::make()
                    ->label('Anzeigen')
                    ->color('gray')
                    ->hidden(fn (Member $record): bool => static::isInactive($record)),
                // Inactive rows are dimmed and not openable; this info action
                // explains why and how to reactivate (admin contact). Mobile-
                // friendly modal instead of a hover tooltip.
                Action::make('inactiveInfo')
                    ->label('Info')
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->color('gray')
                    ->visible(fn (Member $record): bool => static::isInactive($record))
                    ->modalHeading('Mitgliedschaft inaktiv')
                    ->modalIcon(Heroicon::OutlinedInformationCircle)
                    ->modalContent(view('filament.members.inactive-info'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Schließen'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberAccounts::route('/'),
            'view' => ViewMemberAccount::route('/{record}'),
            'logs' => ViewMemberAccountLogs::route('/{record}/logs'),
            'edit' => EditMemberAccount::route('/{record}/edit'),
        ];
    }
}
