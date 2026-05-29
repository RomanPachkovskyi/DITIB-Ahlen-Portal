<?php

use App\Http\Controllers\MemberProfilePhotoController;
use App\Http\Controllers\MemberMagicLoginController;
use App\Livewire\MembershipForm;
use App\Livewire\PhotoUploadPoc;
use Illuminate\Support\Facades\Route;

Route::get('/', MembershipForm::class)->name('home');
Route::get('/members/{member}/profile-photo', MemberProfilePhotoController::class)
    ->name('members.profile-photo');
Route::get('/konto/zugang/{token}', MemberMagicLoginController::class)
    ->name('member.magic-login.consume');

if (app()->isLocal()) {
    Route::get('/photo-upload-poc', PhotoUploadPoc::class)
        ->name('photo-upload-poc');
}
