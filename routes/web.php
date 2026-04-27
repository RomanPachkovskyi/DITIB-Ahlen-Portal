<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\MembershipForm::class)->name('home');
