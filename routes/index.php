<?php

use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return response()->json(['message' => 'Endpoint not found'], 404);
});

Route::get('/', [IndexController::class, 'index']);
Route::get('/favicon.ico', function () {
    return response()->file(public_path('favicon.ico'));
});

Route::prefix('/console')->group(function () {
    require_once __DIR__.'/../routes/console.php';
})->middleware(['dev.env']);

Route::prefix('/storage')->group(function () {
    require_once __DIR__.'/../routes/storage.php';
});

Route::prefix('/api')->group(function () {
    require_once __DIR__.'/../routes/api.php';
});
