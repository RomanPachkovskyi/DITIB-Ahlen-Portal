<?php

namespace App\Filament\Member\Resources\MemberAccounts\Pages;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Schemas\MemberFormContext;
use App\Mail\MemberUpdatedByMemberNotification;
use App\Services\EmailLogger;
use App\Services\MemberAuditLogger;
use App\Support\MemberStatus;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class EditMemberAccount extends EditRecord
{
    protected static string $resource = MemberAccountResource::class;

    /** Status of the record before this save, used to decide the processing transition. */
    protected ?string $statusBeforeSave = null;

    /**
     * Decrypted editable-field values captured before the save, so we can
     * compute real changes (and correct masking) regardless of encryption.
     *
     * @var array<string, mixed>
     */
    protected array $oldEditableValues = [];

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

        // Snapshot original (decrypted) editable values before the form is applied.
        foreach (MemberFormContext::memberEditableFields() as $field) {
            $this->oldEditableValues[$field] = $this->record->getAttribute($field);
        }

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
        // Compute real changes from decrypted old vs new values. This avoids the
        // encrypted-cast false positive (random IV makes getChanges() report
        // unchanged IBAN as changed) and yields correct masking.
        $changes = [];
        foreach (MemberFormContext::memberEditableFields() as $field) {
            $old = $this->oldEditableValues[$field] ?? null;
            $new = $this->record->getAttribute($field);

            if ($this->scalarize($old) !== $this->scalarize($new)) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        if ($changes === []) {
            return; // no-op save: no status change, no log, no email
        }

        // Any real member change moves an open/active record into processing so
        // an admin reviews it.
        if (in_array($this->statusBeforeSave, [MemberStatus::PENDING, MemberStatus::ACTIVE], true)) {
            $this->record->status = MemberStatus::PROCESSING;
            $this->record->saveQuietly();
        }

        // Audit log (new decrypted values → correct IBAN/BIC masking).
        app(MemberAuditLogger::class)->memberUpdated(
            $this->record,
            array_map(fn (array $pair): mixed => $pair['new'], $changes),
            'member',
        );

        $this->notifyAdmin($changes);
    }

    /**
     * @param  array<string, array{old: mixed, new: mixed}>  $changes
     */
    protected function notifyAdmin(array $changes): void
    {
        $described = app(MemberAuditLogger::class)->describeChanges($changes);
        $adminUrl = MemberResource::getUrl('view', ['record' => $this->record], panel: 'admin');

        // Synchronous send like other project emails. An SMTP failure must not
        // break the save — log it and move on.
        try {
            Mail::to('info@ditib-ahlen-projekte.de')
                ->send(new MemberUpdatedByMemberNotification($this->record, $described, $adminUrl));
            app(EmailLogger::class)->sent('admin_member_updated', MemberUpdatedByMemberNotification::class, 'admin', 'info@ditib-ahlen-projekte.de', $this->record);
        } catch (Throwable $exception) {
            app(EmailLogger::class)->failed('admin_member_updated', MemberUpdatedByMemberNotification::class, 'admin', 'info@ditib-ahlen-projekte.de', $exception, $this->record);
            Log::error('Member self-edit admin notification failed.', [
                'member_id' => $this->record->id,
                'member_number' => $this->record->member_number,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function scalarize(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value instanceof \DateTimeInterface) {
            return \Illuminate\Support\Carbon::instance($value)->toDateTimeString();
        }

        return (string) $value;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
