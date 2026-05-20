<?php

namespace App\Filament\Member\Resources\MemberAccounts\Pages;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMemberAccount extends ViewRecord
{
    protected static string $resource = MemberAccountResource::class;

    public function getBreadcrumb(): string
    {
        return 'Anzeigen';
    }

    public function getTitle(): string
    {
        return $this->record->full_name;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
