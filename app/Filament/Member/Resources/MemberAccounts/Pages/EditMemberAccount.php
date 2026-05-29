<?php

namespace App\Filament\Member\Resources\MemberAccounts\Pages;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Filament\Resources\Members\Schemas\MemberFormContext;
use App\Support\MemberStatus;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditMemberAccount extends EditRecord
{
    protected static string $resource = MemberAccountResource::class;

    /** Status of the record before this save, used to decide the processing transition. */
    protected ?string $statusBeforeSave = null;

    public function getBreadcrumb(): string
    {
        return 'Bearbeiten';
    }

    public function getTitle(): string
    {
        return $this->record->full_name.' bearbeiten';
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Abbrechen')
            ->url($this->getResource()::getUrl('view', ['record' => $this->record]));
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Änderungen speichern')
            ->color('primary');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->statusBeforeSave = $this->record->status;

        // Security boundary: only allowlisted member fields survive, regardless
        // of what the request payload contains (status, admin_notiz, email,
        // member_number, consent fields are dropped here).
        $data = MemberFormContext::onlyMemberEditable($data);

        // Roman's decision 5: switching to Lastschrift needs a fresh SEPA consent.
        // The in-form re-consent step ships in a follow-up (3c); until then a
        // member cannot move to Lastschrift without an existing consent on file.
        if (($data['zahlungsart'] ?? null) === 'lastschrift' && ! $this->record->sepa_zustimmung) {
            throw ValidationException::withMessages([
                'data.zahlungsart' => 'Für die Umstellung auf Lastschrift ist eine erneute SEPA-Zustimmung erforderlich. Bitte wenden Sie sich an DITIB Ahlen.',
            ]);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $changedEditableFields = array_intersect(
            array_keys($this->record->getChanges()),
            MemberFormContext::memberEditableFields(),
        );

        // Any real member change moves an open/active record into processing so
        // an admin reviews it. No-op saves leave the status untouched.
        if ($changedEditableFields !== []
            && in_array($this->statusBeforeSave, [MemberStatus::PENDING, MemberStatus::ACTIVE], true)
        ) {
            $this->record->status = MemberStatus::PROCESSING;
            $this->record->saveQuietly();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
