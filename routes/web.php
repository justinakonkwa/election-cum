<?php

use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\VoterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/election/status', [HomeController::class, 'status'])->name('election.status');

Route::get('/vote', [VoteController::class, 'ballot'])->name('vote.ballot');
Route::redirect('/vote/bulletin', '/vote');
Route::post('/vote/review', [VoteController::class, 'review'])->name('vote.review');
Route::get('/vote/confirm', [VoteController::class, 'confirm'])->name('vote.confirm');
Route::post('/vote/cast', [VoteController::class, 'cast'])
    ->middleware('throttle:10,1')
    ->name('vote.cast');

Route::get('/vote/success', [VoteController::class, 'success'])->name('vote.success');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('admin.login.store');
});

Route::middleware(['auth', 'can:manage-election'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/election/status', [DashboardController::class, 'updateStatus'])->name('election.status');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::put('/positions/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');

    Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
    Route::post('/candidates', [CandidateController::class, 'store'])->name('candidates.store');
    Route::put('/candidates/{candidate}', [CandidateController::class, 'update'])->name('candidates.update');
    Route::delete('/candidates/{candidate}', [CandidateController::class, 'destroy'])->name('candidates.destroy');

    Route::get('/voters', [VoterController::class, 'index'])->name('voters.index');
    Route::post('/voters', [VoterController::class, 'store'])->name('voters.store');
    Route::post('/voters/import', [VoterController::class, 'import'])->name('voters.import');
    Route::put('/voters/{voter}', [VoterController::class, 'update'])->name('voters.update');
});
