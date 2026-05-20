<?php

namespace App\Filament\Member\Resources\MemberAccounts\Pages;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use Filament\Resources\Pages\ListRecords;

class ListMemberAccounts extends ListRecords
{
    protected static string $resource = MemberAccountResource::class;

    public function getTitle(): string
    {
        return 'Meine Mitgliedschaften';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
