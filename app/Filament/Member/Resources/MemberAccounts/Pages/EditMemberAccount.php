<?php

namespace App\Filament\Member\Resources\MemberAccounts\Pages;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Filament\Resources\Members\Schemas\MemberFormContext;
use App\Services\MemberAuditLogger;
use App\Support\MemberStatus;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditMemberAccount extends EditRecord
{
    protected static string $resource = MemberAccountResource::class;

    /** Status of the record before this save, used to decide the processing transition. */
    protected ?string $statusBeforeSave = null;

    /**
     * @var array<string, mixed>
     */
    protected array $changesBeforeProcessingTransition = [];

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

        // Transient confirmation field (not a Member column); read before the
        // allowlist drops it.
        $reconsent = (bool) ($data['sepa_reconsent'] ?? false);

        // Security boundary: only allowlisted member fields survive, regardless
        // of what the request payload contains (status, admin_notiz, email,
        // member_number, consent fields are dropped here).
        $data = MemberFormContext::onlyMemberEditable($data);

        // Roman's decision 5: any bank-data change requires a fresh SEPA mandate.
        // sepa_zustimmung / sepa_zustimmung_at are system-controlled and set only
        // here, on explicit re-consent. zustimmung_at (application/DSGVO consent)
        // is never touched.
        $bankFields = ['zahlungsart', 'iban', 'bic', 'kontoinhaber', 'kreditinstitut'];
        $bankChanged = false;
        foreach ($bankFields as $field) {
            if (array_key_exists($field, $data)
                && (string) $data[$field] !== (string) $this->record->getAttribute($field)) {
                $bankChanged = true;
                break;
            }
        }

        $willBeLastschrift = ($data['zahlungsart'] ?? $this->record->zahlungsart) === 'lastschrift';

        if ($willBeLastschrift && $bankChanged) {
            if (! $reconsent) {
                throw ValidationException::withMessages([
                    'data.sepa_reconsent' => 'Bitte bestätigen Sie das SEPA-Lastschriftmandat, um die Bankverbindung zu ändern.',
                ]);
            }

            $data['sepa_zustimmung'] = true;
            $data['sepa_zustimmung_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $changedEditableFields = array_intersect(
            array_keys($this->record->getChanges()),
            MemberFormContext::memberEditableFields(),
        );
        $this->changesBeforeProcessingTransition = array_intersect_key(
            $this->record->getChanges(),
            array_flip(MemberFormContext::memberEditableFields()),
        );

        // Any real member change moves an open/active record into processing so
        // an admin reviews it. No-op saves leave the status untouched.
        if ($changedEditableFields !== []
            && in_array($this->statusBeforeSave, [MemberStatus::PENDING, MemberStatus::ACTIVE], true)
        ) {
            $this->record->status = MemberStatus::PROCESSING;
            $this->record->saveQuietly();
        }

        app(MemberAuditLogger::class)->memberUpdated(
            $this->record,
            $this->changesBeforeProcessingTransition,
            'member',
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
