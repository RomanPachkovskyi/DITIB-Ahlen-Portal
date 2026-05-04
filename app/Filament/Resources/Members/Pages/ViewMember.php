<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    public function getBreadcrumb(): string
    {
        return __('Vorschau');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $this->record->full_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Bearbeiten'),
        ];
    }
}
