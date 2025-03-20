<?php

use App\Http\Controllers\BrowserAgentController;
use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return response()->json(['message' => 'Endpoint not found'], 404);
});

Route::get('/', function () {
    return response()->json(['message' => "Welcome to Agrofast's services"], 200);
});
Route::get('/favicon.ico', function () {
    return response()->file(public_path('favicon.ico'));
});

Route::middleware(['dev.env'])->group(function () {
    Route::prefix('/console')->group(function () {
        require_once __DIR__.'/../routes/console.php';
    });

    Route::prefix('/email')->group(function () {
        require_once __DIR__.'/../routes/email.php';
    });
});

Route::middleware([])->prefix('/uploads')->group(function () {
    require_once __DIR__.'/../routes/uploads.php';
});

Route::prefix('/storage')->group(function () {
    require_once __DIR__.'/../routes/storage.php';
});

Route::prefix('/api')->group(function () {
    Route::prefix('fingerprint')->group(function () {
        Route::get('/', [BrowserAgentController::class, 'makeFingerprint']);
        Route::middleware('fingerprint')->get('/validate', [BrowserAgentController::class, 'validate']);
    });

    require_once __DIR__.'/../routes/api.php';
});

Route::prefix('/')->group(function () {
    Route::get('/{type}/{file}', function ($type = null, $file = null) {
        $path = public_path("{$type}/".$file);
        if (file_exists($path)) {
            return response()->file($path);
        }

        return response()->json(['message' => 'File not found'], 404);
    });
});
