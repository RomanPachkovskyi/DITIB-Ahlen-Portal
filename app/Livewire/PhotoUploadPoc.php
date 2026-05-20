<?php

namespace App\Livewire;

use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class PhotoUploadPoc extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $croppedPhoto = null;

    public ?array $photoResult = null;

    public function acceptCroppedPhoto(): void
    {
        $this->validate([
            'croppedPhoto' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg',
                'max:1024',
            ],
        ], [
            'croppedPhoto.required' => 'Bitte übernehmen Sie zuerst ein Foto.',
            'croppedPhoto.image' => 'Das zugeschnittene Foto muss eine Bilddatei sein.',
            'croppedPhoto.mimes' => 'Das zugeschnittene Foto muss als JPEG vorliegen.',
            'croppedPhoto.max' => 'Das zugeschnittene Foto darf maximal 1 MB groß sein.',
        ]);

        if ($this->croppedPhoto === null) {
            throw ValidationException::withMessages([
                'croppedPhoto' => 'Bitte übernehmen Sie zuerst ein Foto.',
            ]);
        }

        $dimensions = getimagesize($this->croppedPhoto->getRealPath()) ?: null;

        $this->photoResult = [
            'name' => $this->croppedPhoto->getClientOriginalName(),
            'mime' => $this->croppedPhoto->getMimeType(),
            'size' => $this->croppedPhoto->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
        ];
    }

    public function removeCroppedPhoto(): void
    {
        $this->reset(['croppedPhoto', 'photoResult']);
        $this->resetValidation('croppedPhoto');
    }

    public function render()
    {
        return view('livewire.photo-upload-poc')
            ->layout('layouts.public');
    }
}
