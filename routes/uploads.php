<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/pictures/{userUuid}')->group(function () {
    Route::get('/{pictureUuid?}', [UserController::class, 'picture']);
});

Route::prefix('/attachment')->group(function () {
    Route::get('/{uuid}', [AssetController::class, 'getAttachment']);
});
