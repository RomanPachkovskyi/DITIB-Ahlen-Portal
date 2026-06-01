<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Services\MemberAuditLogger;
use App\Services\ProfilePhotoService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected ?string $statusBeforeSave = null;

    public function getBreadcrumb(): string
    {
        return __('Bearbeiten');
    }

    public function getTitle(): string|Htmlable
    {
        return $this->record->full_name.' bearbeiten';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadProfilePhoto')
                ->label($this->record->profile_photo_path ? 'Foto ersetzen' : 'Foto hochladen')
                ->icon(Heroicon::OutlinedPhoto)
                ->color('gray')
                ->modalHeading($this->record->profile_photo_path ? 'Profilfoto ersetzen' : 'Profilfoto hochladen')
                ->modalSubmitActionLabel('Foto speichern')
                ->schema([
                    FileUpload::make('profile_photo')
                        ->label('Profilfoto')
                        ->helperText('JPEG, PNG oder WebP bis 8 MB. Das Foto wird als privates JPEG 800 x 800 gespeichert. Nur verwenden, wenn eine Foto-Einwilligung vorliegt.')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(8192)
                        ->storeFiles(false)
                        ->visibility('private')
                        ->imageEditor()
                        ->imageEditorAspectRatioOptions(['1:1'])
                        ->imageResizeMode('cover')
                        ->imageResizeTargetWidth('800')
                        ->imageResizeTargetHeight('800')
                        ->imageResizeUpscale(false)
                        ->required(),
                ])
                ->action(function (array $data, ProfilePhotoService $profilePhotos): void {
                    $file = $data['profile_photo'] ?? null;

                    if (! $file instanceof TemporaryUploadedFile) {
                        throw ValidationException::withMessages([
                            'profile_photo' => 'Bitte wählen Sie ein Profilfoto aus.',
                        ]);
                    }

                    $hadPhoto = $this->record->profile_photo_path !== null;
                    $profilePhotos->store($this->record, $file);
                    app(MemberAuditLogger::class)->photoUploaded($this->record, $hadPhoto, 'admin');
                    $this->record->refresh();
                    $this->fillForm();
                    $this->dispatch('$refresh');

                    Notification::make()
                        ->title('Foto wurde gespeichert.')
                        ->success()
                        ->send();
                }),
            Action::make('deleteProfilePhoto')
                ->label('Foto entfernen')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->visible(fn (): bool => $this->record->profile_photo_path !== null)
                ->requiresConfirmation()
                ->modalHeading('Profilfoto entfernen')
                ->modalDescription('Das Mitglied bleibt erhalten. Nur das gespeicherte Profilfoto wird entfernt.')
                ->modalSubmitActionLabel('Foto entfernen')
                ->action(function (ProfilePhotoService $profilePhotos): void {
                    $profilePhotos->delete($this->record);
                    app(MemberAuditLogger::class)->photoDeleted($this->record, 'admin');
                    $this->record->refresh();
                    $this->fillForm();
                    $this->dispatch('$refresh');

                    Notification::make()
                        ->title('Foto wurde entfernt.')
                        ->success()
                        ->send();
                }),
            Action::make('saveHeader')
                ->label('Änderungen speichern')
                ->color('primary')
                ->extraAttributes(['class' => 'ditib-brand-primary-button'], merge: true)
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
            ->color('primary')
            ->extraAttributes(['class' => 'ditib-brand-primary-button'], merge: true);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Abbrechen')
            ->url($this->getResource()::getUrl('view', ['record' => $this->record]));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->statusBeforeSave = $this->record->status;

        unset(
            $data['sepa_zustimmung'],
            $data['dsgvo_zustimmung'],
            $data['zustimmung_at'],
            $data['profile_photo_zustimmung'],
            $data['profile_photo_zustimmung_at'],
        );

        return $data;
    }

    protected function afterSave(): void
    {
        $changes = $this->record->getChanges();

        if (array_key_exists('status', $changes)) {
            app(MemberAuditLogger::class)->statusChanged(
                $this->record,
                $this->statusBeforeSave,
                $this->record->status,
                'admin',
            );

            unset($changes['status']);
        }

        app(MemberAuditLogger::class)->memberUpdated(
            $this->record,
            $changes,
            'admin',
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
