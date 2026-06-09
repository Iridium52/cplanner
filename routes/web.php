<?php

use App\Livewire\Admin\ProjectTypeManager;
use App\Livewire\Admin\UserManager;
use App\Livewire\Auth\TwoFactorChallenge;
use App\Livewire\Auth\TwoFactorSetup;
use App\Livewire\Dashboard;
use App\Livewire\FlaggedTasks;
use App\Livewire\Projects\ProjectCreate;
use App\Livewire\Projects\ProjectEdit;
use App\Livewire\Projects\ProjectShow;
use App\Livewire\Projects\ProjectSettings;
use App\Livewire\Profile\UserProfile;
use Illuminate\Support\Facades\Route;

// Root → redirect to dashboard or login
Route::get('/', fn() => redirect()->route('dashboard'));

// 2FA routes (auth required, 2FA NOT yet required)
Route::middleware('auth')->group(function () {
    Route::get('/two-factor/setup', TwoFactorSetup::class)->name('two-factor.setup');
    Route::get('/two-factor/challenge', TwoFactorChallenge::class)->name('two-factor.challenge');
});

// Fully authenticated routes (auth + 2FA verified)
Route::middleware(['auth', 'two-factor'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/flagged-tasks', FlaggedTasks::class)->name('flagged-tasks');

    // Projects
    Route::get('/projects/create', ProjectCreate::class)->middleware('admin')->name('projects.create');
    Route::get('/projects/{project}', ProjectShow::class)->name('projects.show');
    Route::get('/projects/{project}/edit', ProjectEdit::class)->middleware('admin')->name('projects.edit');
    Route::get('/projects/{project}/settings', ProjectSettings::class)->middleware('admin')->name('projects.settings');

    // Attachment file serving (local disk, private)
    Route::get('/attachments/{attachment}', function (\App\Models\TaskAttachment $attachment) {
        $path = \Illuminate\Support\Facades\Storage::disk('local')->path($attachment->path);
        abort_unless(file_exists($path), 404);
        return response()->file($path, ['Content-Type' => $attachment->mime_type]);
    })->name('attachments.serve');

    // Profile
    Route::get('/profile', UserProfile::class)->name('profile');

    // Admin only
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', UserManager::class)->name('users');
        Route::get('/project-types', ProjectTypeManager::class)->name('project-types');
    });
});

require __DIR__.'/auth.php';
