<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentUploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StrategyController;

// Public: redirect root to login
Route::get('/', fn () => redirect()->route('login'));

// Pending — shown after register, before role is assigned
Route::get('/pending', fn () => view('auth.pending'))->middleware('auth')->name('pending');

// All authenticated app routes
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::get('/strategy', fn () => view('strategy.index'))->name('strategy');
    Route::get('/strategy/new', fn () => view('strategy.new'))->name('strategy.new');
    Route::get('/strategy/{id}/slides', [StrategyController::class, 'downloadSlides'])->name('strategy.slides');

    Route::get('/goals', fn () => view('goals.index'))->name('goals.index');
    Route::get('/goals/{goal}', fn ($goal) => view('goals.show', ['goalId' => $goal]))->name('goals.show');

    Route::get('/audits', fn () => view('audits.index'))->name('audits.index');
    Route::get('/audits/{auditId}', fn ($auditId) => view('audits.checklist', ['auditId' => $auditId]))->name('audits.checklist');
    Route::get('/audits/{auditId}/report', fn ($auditId) => view('audits.report', ['auditId' => $auditId]))->name('audits.report');

    Route::get('/recommendations', fn () => view('recommendations.index'))->name('recommendations');
    Route::get('/insights', fn () => view('insights.index'))->name('insights');
    Route::get('/dataset', fn () => view('dataset.index'))->name('dataset');
    Route::post('/dataset/upload-document', [DocumentUploadController::class, 'store'])->name('dataset.upload-document')->middleware('role:super_admin,agency_staff');

    // Agency-only routes
    Route::get('/agent', fn () => view('agent.index'))->middleware('role:super_admin,agency_staff')->name('agent');

    // Super admin only
    Route::get('/admin', fn () => view('admin.index'))->middleware('role:super_admin')->name('admin.index');

    // Breeze profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
