<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\MachineryController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\JwtMiddleware;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::fallback(function () {
    return response()->json(['message' => 'Endpoint not found'], 404);
});

// Rota inicial
Route::get('/', [IndexController::class, 'handleApiIndex']);
Route::get('/favicon.ico', [IndexController::class, 'favicon']);

// Debug routes
Route::prefix('debug')->group(function () {
    Route::get('/', [DebugController::class, 'showEnvironment']);
    Route::prefix('env')->group(function () {
        Route::get('/', [DebugController::class, 'getEnvironmentInstructions']);
        Route::get('/{variable}', [DebugController::class, 'getEnvironmentVariable']);
    });
    Route::get('/lasterror', [DebugController::class, 'getLastError']);
    Route::get('/dir', [DebugController::class, 'mapProjectFiles']);
    Route::get('/file', [DebugController::class, 'getFileContent']);
    Route::post('/body', [DebugController::class, 'showBody']);
})->middleware('environment');

// User routes
Route::prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'getUser']);
    Route::put('/', [UserController::class, 'updateUser'])->middleware(JwtMiddleware::class);
    Route::post('/', [UserController::class, 'createUser']);
    Route::get('/info', [UserController::class, 'getUserInfo'])->middleware(JwtMiddleware::class);
    Route::get('/exists', [UserController::class, 'checkIfExists']);
    Route::get('/auth', [UserController::class, 'authenticateUser'])->middleware(JwtMiddleware::class);
    Route::post('/login', [UserController::class, 'userLogin']);
    Route::get('/resend-code', [UserController::class, 'resendCode'])->middleware(JwtMiddleware::class);
});

// Chat routes
Route::middleware('auth.jwt')->group(function () {
    Route::get('/chat', [ChatController::class, 'getUserchat']);
});
Route::middleware('auth.jwt')->prefix('message')->group(function () {
    Route::get('/{chatUuid}', [MessageController::class, 'getmessage']); // Get message in a chat
    Route::post('/', [MessageController::class, 'sendMessage']); // Send a message
    Route::delete('/{id}', [MessageController::class, 'deleteMessage']); // Delete a message
});

// Machinery routes
Route::prefix('machinery')->middleware('auth.user')->group(function () {
    Route::get('/', [MachineryController::class, 'listMachinery']);
    Route::post('/create', [MachineryController::class, 'createMachine']);
    Route::put('/update', [MachineryController::class, 'updateMachine']);
    Route::delete('/disable', [MachineryController::class, 'disableMachine']);
});

// Transport vehicle routes
Route::middleware('auth.jwt')->prefix('transport')->group(function () {
    Route::get('/', [CarrierController::class, 'listTransports']);
    Route::post('/create', [CarrierController::class, 'createTransport']);
    Route::put('/update', [CarrierController::class, 'updateTransport']);
    Route::delete('/disable', [CarrierController::class, 'disableTransport']);
});

// Request routes
Route::middleware('auth.jwt')->prefix('request')->group(function () {
    Route::get('/', [RequestController::class, 'listRequests']);
    Route::post('/create', [RequestController::class, 'makeRequest']);
    Route::put('/update', [RequestController::class, 'updateRequest']);
    Route::delete('/cancel', [RequestController::class, 'cancelRequest']);
});

// Offer routes
Route::middleware('auth.jwt')->prefix('offer')->group(function () {
    Route::get('/', [OfferController::class, 'listOffers']);
    Route::post('/create', [OfferController::class, 'makeOffer']);
    Route::put('/update', [OfferController::class, 'updateOffer']);
    Route::delete('/cancel', [OfferController::class, 'cancelOffer']);
});
