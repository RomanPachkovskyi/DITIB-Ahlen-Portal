<?php

namespace App\Filament\Member\Resources\MemberAccounts;

use App\Filament\Member\Resources\MemberAccounts\Pages\ListMemberAccounts;
use App\Filament\Member\Resources\MemberAccounts\Pages\ViewMemberAccount;
use App\Models\Member;
use App\Support\MemberStatus;
use App\Support\PhoneNumber;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

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
        $email = Filament::auth()->user()?->email;

        return $email !== null
            && $record instanceof Member
            && $record->email !== null
            && strcasecmp($record->email, $email) === 0;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mitgliedschaft')
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
                            ]),
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                TextInput::make('full_name')
                                    ->label('Vor- und Nachname')
                                    ->disabled()
                                    ->columnSpan([
                                        'default' => 'full',
                                        'sm' => 1,
                                    ]),
                                TextInput::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn (?string $state): string => $state ? MemberStatus::label($state) : '')
                                    ->disabled(),
                                TextInput::make('birth_date')
                                    ->label('Geburtsdatum')
                                    ->formatStateUsing(fn ($state): string => blank($state) ? '' : Carbon::parse($state)->format('d.m.Y'))
                                    ->disabled(),
                                TextInput::make('phone')
                                    ->label('Telefon')
                                    ->formatStateUsing(fn (?string $state): string => PhoneNumber::format($state))
                                    ->disabled(),
                                TextInput::make('email')
                                    ->label('E-Mail')
                                    ->disabled()
                                    ->columnSpanFull(),
                                TextInput::make('street')
                                    ->label('Straße und Hausnummer')
                                    ->disabled()
                                    ->columnSpanFull(),
                                TextInput::make('postal_code')
                                    ->label('Postleitzahl')
                                    ->disabled(),
                                TextInput::make('city')
                                    ->label('Ort')
                                    ->disabled(),
                                TextInput::make('state')
                                    ->label('Bundesland')
                                    ->disabled(),
                                TextInput::make('monatsbeitrag')
                                    ->label('Monatlicher Beitrag')
                                    ->formatStateUsing(fn ($state): string => $state !== null ? number_format((float) $state, 2, ',', '.').' €' : '')
                                    ->disabled(),
                                TextInput::make('zahlungsart')
                                    ->label('Zahlungsweise')
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'barzahlung' => 'Barzahlung',
                                        'lastschrift' => 'Lastschrift',
                                        'dauerauftrag' => 'Dauerauftrag',
                                        default => $state ?? '',
                                    })
                                    ->disabled(),
                            ]),
                    ]),
            ]);
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
                TextColumn::make('city')
                    ->label('Ort')
                    ->wrap(),
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
