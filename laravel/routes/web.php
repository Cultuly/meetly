<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GithubController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ChannelController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// === Authenticated routes ===
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // === Workspaces ===
    Route::resource('workspaces', WorkspaceController::class);
    
    // === Channels ===
    Route::post('workspaces/{workspace}/channels', [ChannelController::class, 'store'])
        ->name('channels.store');

    Route::delete('channels/{channel}', [ChannelController::class, 'destroy'])
        ->name('channels.destroy');
});

require __DIR__.'/auth.php';

// === GitHub OAuth ===
Route::get('/auth/github', [GithubController::class, 'redirect'])
    ->name('github.redirect');

Route::get('/auth/github/callback', [GithubController::class, 'callback']);