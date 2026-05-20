<?php

use App\Http\Controllers\MemberProfilePhotoController;
use App\Livewire\MembershipForm;
use App\Livewire\PhotoUploadPoc;
use Illuminate\Support\Facades\Route;

Route::get('/', MembershipForm::class)->name('home');
Route::get('/members/{member}/profile-photo', MemberProfilePhotoController::class)
    ->name('members.profile-photo');

if (app()->isLocal()) {
    Route::get('/photo-upload-poc', PhotoUploadPoc::class)
        ->name('photo-upload-poc');
}
