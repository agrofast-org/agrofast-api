<?php

use App\Http\Controllers\AssetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Agrofast data bucket',
    ], 200);
});

Route::post('/upload', [AssetController::class, 'upload']);
Route::get('/all', [AssetController::class, 'all']);
Route::get('/last', [AssetController::class, 'last']);
