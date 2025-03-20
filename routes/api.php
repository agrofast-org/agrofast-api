<?php

use App\Http\Controllers\CarrierController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\MachineryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'index']);

// Debug routes
Route::middleware(['dev.env'])->prefix('/debug')->group(function () {
    Route::get('/', [DebugController::class, 'showEnvironment']);
    Route::prefix('/env')->group(function () {
        Route::get('/', [DebugController::class, 'getEnvironmentInstructions']);
        Route::get('/{variable}', [DebugController::class, 'getEnvironmentVariable']);
    });
    Route::get('/lasterror', [DebugController::class, 'getLastError']);
    Route::get('/dir', [DebugController::class, 'mapProjectFiles']);
    Route::get('/file', [DebugController::class, 'getFileContent']);
    Route::post('/body', [DebugController::class, 'showBody']);

    Route::prefix('/test')->group(function () {
        Route::prefix('/job')->group(function () {
            Route::get('/email', [DebugController::class, 'sendEmailJob']);
            // Route::get('/sms', [DebugController::class, 'sendSmsJob']);
        });
        Route::get('/email', [DebugController::class, 'sendEmail']);
        // Route::get('/sms', [DebugController::class, 'sendSms']);
    });
});

// User routes
Route::middleware(['db.safe', 'fingerprint'])->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::get('/code-length', [UserController::class, 'codeLength']);
    });

    Route::prefix('/user')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/', [UserController::class, 'update'])->middleware(['auth']);
        Route::prefix('/info')->middleware(['auth.basic'])->group(function () {
            Route::get('/me', [UserController::class, 'self']);
            Route::get('/{uuid}', [UserController::class, 'info']);
        });
        Route::prefix('/picture')->middleware(['auth'])->group(function () {
            Route::post('/upload', [UserController::class, 'postPicture']);
        });
        Route::get('/exists', [UserController::class, 'exists']);
        Route::middleware(['auth.basic'])->prefix('/auth')->group(function () {
            Route::get('/', [UserController::class, 'authenticate']);
            Route::get('/methods', [UserController::class, 'authenticationMethods']);
        });
        Route::post('/login', [UserController::class, 'login']);
        Route::get('/resend-code', [UserController::class, 'resendCode'])->middleware(['auth.basic']);
    });
    Route::middleware(['auth'])->group(function () {
        // Chat routes
        Route::get('/chat', [ChatController::class, 'getUserchat']);

        Route::prefix('/message')->group(function () {
            Route::get('/{chatUuid}', [MessageController::class, 'getMessage']); // Get message in a chat
            Route::post('/', [MessageController::class, 'sendMessage']); // Send a message
            Route::delete('/{id}', [MessageController::class, 'deleteMessage']); // Delete a message
        });

        // Machinery routes
        Route::prefix('/machinery')->group(function () {
            Route::get('/', [MachineryController::class, 'listMachinery']);
            Route::post('/create', [MachineryController::class, 'createMachine']);
            Route::put('/update', [MachineryController::class, 'updateMachine']);
            Route::delete('/disable', [MachineryController::class, 'disableMachine']);
        });

        // Transport vehicle routes
        Route::prefix('/transport')->group(function () {
            Route::get('/', [CarrierController::class, 'listTransports']);
            Route::post('/create', [CarrierController::class, 'createTransport']);
            Route::put('/update', [CarrierController::class, 'updateTransport']);
            Route::delete('/disable', [CarrierController::class, 'disableTransport']);
        });

        // Request routes
        Route::prefix('/request')->group(function () {
            Route::get('/', [RequestController::class, 'listRequests']);
            Route::post('/create', [RequestController::class, 'makeRequest']);
            Route::put('/update', [RequestController::class, 'updateRequest']);
            Route::delete('/cancel', [RequestController::class, 'cancelRequest']);
        });

        // Offer routes
        Route::prefix('/offer')->group(function () {
            Route::get('/', [OfferController::class, 'listOffers']);
            Route::post('/create', [OfferController::class, 'makeOffer']);
            Route::put('/update', [OfferController::class, 'updateOffer']);
            Route::delete('/cancel', [OfferController::class, 'cancelOffer']);
        });
    });
});
