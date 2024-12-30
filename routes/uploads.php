<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/pictures/{userId}')->group(function () {
    Route::get('/{pictureUuid?}', [UserController::class, 'picture']);
});
