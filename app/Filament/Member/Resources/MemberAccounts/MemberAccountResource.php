<?php

namespace App\Filament\Member\Resources\MemberAccounts;

use App\Filament\Member\Resources\MemberAccounts\Pages\ListMemberAccounts;
use App\Filament\Member\Resources\MemberAccounts\Pages\ViewMemberAccount;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Members\Schemas\MemberFormContext;
use App\Models\Member;
use App\Support\MemberStatus;
use BackedEnum;
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
        return static::belongsToCurrentUser($record);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        // Self-service edit is enabled in Phase 3; read-only for now.
        return false;
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
            ->recordUrl(fn (Member $record): string => static::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make()
                    ->label('Anzeigen')
                    ->color('gray'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberAccounts::route('/'),
            'view' => ViewMemberAccount::route('/{record}'),
        ];
    }
}
