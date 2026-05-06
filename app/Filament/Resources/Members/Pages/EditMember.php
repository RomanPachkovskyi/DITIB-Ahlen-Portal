<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    public function getBreadcrumb(): string
    {
        return __('Bearbeiten');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $this->record->full_name . ' bearbeiten';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('saveHeader')
                ->label('Änderungen speichern')
                ->color('success')
                ->action(function (): void {
                    $this->save();
                }),
            Action::make('cancelHeader')
                ->label('Abbrechen')
                ->color('gray')
                ->url($this->getResource()::getUrl('view', ['record' => $this->record])),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Änderungen speichern')
            ->color('success');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Abbrechen')
            ->url($this->getResource()::getUrl('view', ['record' => $this->record]));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['sepa_zustimmung'], $data['dsgvo_zustimmung'], $data['zustimmung_at']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
