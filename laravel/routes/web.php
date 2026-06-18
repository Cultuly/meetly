<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GithubController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\MessageController;

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

    Route::post('workspaces/{workspace}/join', [WorkspaceController::class, 'join'])
    ->name('workspaces.join');
    
    // === Channels ===
    Route::get('channels/{channel}', [ChannelController::class, 'show'])
        ->name('channels.show');

    Route::post('workspaces/{workspace}/channels', [ChannelController::class, 'store'])
        ->name('channels.store');

    Route::delete('channels/{channel}', [ChannelController::class, 'destroy'])
        ->name('channels.destroy');
    
    // === Messages ===
    Route::post('channels/{channel}/messages', [MessageController::class, 'store'])
        ->name('messages.store');

    Route::delete('messages/{message}', [MessageController::class, 'destroy'])
        ->name('messages.destroy');
        
});

require __DIR__.'/auth.php';

// === GitHub OAuth ===
Route::get('/auth/github', [GithubController::class, 'redirect'])
    ->name('github.redirect');

Route::get('/auth/github/callback', [GithubController::class, 'callback']);