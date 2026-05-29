<?php

namespace App\Filament\Member\Resources\MemberAccounts\Pages;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMemberAccount extends ViewRecord
{
    protected static string $resource = MemberAccountResource::class;

    public function getBreadcrumb(): string
    {
        return 'Vorschau';
    }

    public function getTitle(): string
    {
        return $this->record->full_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Shown only when the record is editable (own, non-inactive).
            EditAction::make()
                ->label('Bearbeiten')
                ->color('primary'),
        ];
    }
}
