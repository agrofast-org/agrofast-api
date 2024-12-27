<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\FilesImage;
use Illuminate\Support\Facades\Auth;

Route::get("/", function () {
    return response()->json([
        'message' => 'Agrofast data bucket',
    ], 200);
});

Route::post('/upload', function (Request $request) {
    if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
        return response()->json(['message' => 'Invalid image upload'], 400);
    }
    $file = $request->file('image');
    $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

    $path = $file->storeAs('images', $fileName, 'minio');

    $user = Auth::user();

    $fileRecord = FilesImage::create([
        'id' => Str::uuid(),
        'name' => $file->getClientOriginalName(),
        'path' => $path,
        'mime_type' => $file->getMimeType(),
        'size' => $file->getSize(),
        'uploaded_by' => $user->id ?? null,
    ]);

    return response()->json([
        'message' => 'Image uploaded successfully',
        'file' => $fileRecord
    ], 201);
});


Route::get('/ ', function (Request $request) {
    $files = Storage::allFiles('images');
    return response()->json($files);
});

Route::get('/last', function () {
    $files = Storage::files('images');
    if (empty($files)) {
        return response()->json(['message' => 'No images found'], 404);
    }
    $lastFile = end($files);
    $file = Storage::get($lastFile);
    $type = Storage::mimeType($lastFile);

    return response($file, 200)->header('Content-Type', $type);
});

Route::get('/image/{filename}', function ($filename) {
    $path = 'images/' . $filename;

    if (!Storage::exists($path)) {
        return response()->json(['message' => 'Image not found'], 404);
    }

    $file = Storage::get($path);
    $type = Storage::mimeType($path);

    return response($file, 200)->header('Content-Type', $type);
});
