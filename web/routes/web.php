<?php

use App\Http\Controllers\PredictionController;
use App\Models\FootballMatch;
use Illuminate\Support\Facades\Route;

Route::model('match', FootballMatch::class);

Route::get('/', [PredictionController::class, 'index'])->name('matches.index');
Route::post('/matches/{match}/predict', [PredictionController::class, 'predict'])->name('matches.predict');
Route::post('/matches/sync', [PredictionController::class, 'sync'])->name('matches.sync');
