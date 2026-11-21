<?php

use App\Http\Controllers\ArcPublicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChecklistItemController;
use App\Http\Controllers\ChecklistSectionController;
use App\Http\Controllers\ContentReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DlsReviewController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FinalReviewController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\QualityCardController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/publikasi', [PublicationController::class, 'index'])->name('publications.index');
    Route::get('/publikasi/{publication}', [PublicationController::class, 'show'])->name('publications.show');
    Route::get('/histori', [HistoryController::class, 'index'])->name('history.index');

    Route::get('/file/naskah/{publicationFile}', [FileController::class, 'publicationFile'])->name('files.publication');
    Route::get('/file/revisi/{revision}', [FileController::class, 'revisionFile'])->name('files.revision');
    Route::get('/file/kartu-kendali/{qualityCard}', [FileController::class, 'qualityCard'])->name('files.quality-card');

    Route::middleware('role:tim_naskah')->group(function () {
        Route::get('/pengajuan/buat', [PublicationController::class, 'create'])->name('publications.create');
        Route::get('/pengajuan/buat/{type}', [PublicationController::class, 'createForm'])->name('publications.create-form');
        Route::post('/pengajuan', [PublicationController::class, 'store'])->name('publications.store');
        Route::get('/publikasi/{publication}/perbaiki', [PublicationController::class, 'edit'])->name('publications.edit');
        Route::put('/publikasi/{publication}', [PublicationController::class, 'update'])->name('publications.update');
    });

    Route::middleware('role:ketua_tim')->group(function () {
        Route::get('/review-konten', [ContentReviewController::class, 'index'])->name('reviews.content.index');
        Route::get('/publikasi/{publication}/kartu-kendali', [QualityCardController::class, 'create'])->name('quality-cards.create');
        Route::post('/publikasi/{publication}/kartu-kendali', [QualityCardController::class, 'store'])->name('quality-cards.store');
        Route::get('/anggota-tim', [TeamMemberController::class, 'index'])->name('team-members.index');
        Route::post('/anggota-tim', [TeamMemberController::class, 'store'])->name('team-members.store');
        Route::delete('/anggota-tim/{user}', [TeamMemberController::class, 'destroy'])->name('team-members.destroy');
    });

    Route::middleware('role:ketua_tim,petugas_dls,ketua_dls')->group(function () {
        Route::post('/publikasi/{publication}/revisi', [RevisionController::class, 'store'])->name('revisions.store');
    });

    Route::middleware('role:petugas_dls')->group(function () {
        Route::get('/review-dls', [DlsReviewController::class, 'index'])->name('reviews.dls.index');
        Route::post('/publikasi/{publication}/teruskan', [DlsReviewController::class, 'forward'])->name('reviews.dls.forward');
        Route::resource('checklist-sections', ChecklistSectionController::class)->except(['index', 'show']);
        Route::resource('checklist-items', ChecklistItemController::class)->except(['show']);
        Route::resource('arc-publications', ArcPublicationController::class)->except(['show']);
        Route::get('/ketua-tim', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/ketua-tim/buat', [TeamController::class, 'create'])->name('teams.create');
        Route::post('/ketua-tim', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/ketua-tim/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
        Route::put('/ketua-tim/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('/ketua-tim/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
        Route::get('/unduh-dokumen', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/publikasi/{publication}/unduh', [DocumentController::class, 'download'])->name('documents.download');
    });

    Route::middleware('role:ketua_dls')->group(function () {
        Route::get('/pengecekan-akhir', [FinalReviewController::class, 'index'])->name('reviews.final.index');
        Route::post('/publikasi/{publication}/setujui', [FinalReviewController::class, 'approve'])->name('reviews.final.approve');
        Route::get('/kelola-user', [UserController::class, 'index'])->name('users.index');
        Route::get('/kelola-user/buat', [UserController::class, 'create'])->name('users.create');
        Route::post('/kelola-user', [UserController::class, 'store'])->name('users.store');
        Route::get('/kelola-user/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/kelola-user/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/kelola-user/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});