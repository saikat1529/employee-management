<?php

use App\Http\Controllers\DocsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Password-protected documents (HTML files live in resources/docs, not in public).
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/', [DocsController::class, 'showLogin'])->name('login');
    Route::post('/login', [DocsController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.submit');
    Route::post('/logout', [DocsController::class, 'logout'])->name('logout');
    Route::get('/index', [DocsController::class, 'index'])->name('index');
    Route::get('/{name}', [DocsController::class, 'show'])
        ->where('name', '[a-z0-9-]+')
        ->name('show');
});
