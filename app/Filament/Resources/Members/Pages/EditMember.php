<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    public function getBreadcrumb(): string
    {
        return $this->record->full_name;
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $this->record->full_name . ' bearbeiten';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Löschen'),
        ];
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()->label('Änderungen speichern');
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()->label('Abbrechen');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
