<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/pictures/{userUuid}')->group(function () {
    Route::get('/{pictureUuid?}', [UserController::class, 'picture']);
});

Route::prefix('/attachments')->group(function () {
    Route::post('/', [AssetController::class, 'store']);
    Route::get('/{uuid}', [AssetController::class, 'show']);
});
