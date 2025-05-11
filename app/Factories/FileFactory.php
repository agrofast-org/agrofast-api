<?php

namespace App\Factories;

use App\Models\File\File;
use App\Models\Hr\User;
use Illuminate\Support\Str;

class FileFactory
{
    public static function create($file, string $type): File
    {
        $uuid = Str::uuid();

        $fileName = $uuid.'.'.$file->getClientOriginalExtension();
        $disk = env('FILESYSTEM_DISK', 's3');

        $path = $file->storeAs("/uploads/{$type}", $fileName, $disk);

        $appUrl = env('APP_URL');

        return File::create([
            'uuid' => $uuid,
            'name' => $file->getClientOriginalName(),
            'path' => "{$appUrl}/{$path}",
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => User::auth()->id,
            'active' => true,
        ]);
    }
}
