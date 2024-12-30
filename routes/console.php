<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Console endpoint']);
});
Route::get('/migrate', function () {
    $output = Artisan::call('migrate');

    return response()->json(['output' => Artisan::output()]);
});
