<?php

namespace Tests\Feature;

use App\Livewire\PhotoUploadPoc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class PhotoUploadPocTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_accepts_cropped_jpeg_from_livewire_upload(): void
    {
        Livewire::test(PhotoUploadPoc::class)
            ->set('croppedPhoto', UploadedFile::fake()->image('profile-photo-poc.jpg', 800, 800)->size(200))
            ->call('acceptCroppedPhoto')
            ->assertHasNoErrors()
            ->assertSet('photoResult.mime', 'image/jpeg')
            ->assertSet('photoResult.width', 800)
            ->assertSet('photoResult.height', 800);
    }

    public function test_it_rejects_non_jpeg_cropped_upload(): void
    {
        Livewire::test(PhotoUploadPoc::class)
            ->set('croppedPhoto', UploadedFile::fake()->image('profile-photo-poc.png', 800, 800)->size(200))
            ->call('acceptCroppedPhoto')
            ->assertHasErrors(['croppedPhoto']);
    }
}
