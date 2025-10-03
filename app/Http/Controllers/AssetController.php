<?php

namespace App\Http\Controllers;

use App\Factories\FileFactory;
use App\Http\Requests\Assets\FileAttachmentRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function store(FileAttachmentRequest $request)
    {
        $validated = $request->validated();
        $files = [];

        if (isset($validated['file'])) {
            $file = $validated['file'];
            $fileRecord = FileFactory::create(
                $file,
                'attachments',
            );
            $files[] = $fileRecord;
        } else {
            foreach ($validated['files'] as $file) {
                $fileRecord = FileFactory::create(
                    $file,
                    'attachments',
                );
                $files[] = $fileRecord;
            }
        }

        return response()->json($files, 201);
    }

    public function show(string $uuid)
    {
        $path = "uploads/attachments/{$uuid}";
        $file = Storage::get($path);
        if (empty($file)) {
            return response()->json(['message' => 'No images found'], 404);
        }

        $type = Storage::mimeType($path);

        return response($file, 200)->header('Content-Type', $type);
    }
}
