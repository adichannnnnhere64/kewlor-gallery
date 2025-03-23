<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\LiveEventGalleryController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('home', '/')->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('email/verify/{id}/{hash}', EmailVerificationController::class)
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('logout', LogoutController::class)
        ->name('logout');

    Route::post('upload', [UploadController::class, 'store'])->name('upload');
    Route::post('live-event', [LiveEventGalleryController::class, 'store'])->name('live-event.create');
});
