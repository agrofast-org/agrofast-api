<?php

namespace App\Http\Controllers;

use App\Factories\FileFactory;
use App\Factories\ResponseFactory;
use App\Http\Requests\Assets\FileAttachmentRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function upload(Request $request)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function all(Request $request)
    {
        $files = Storage::allFiles('images');

        return response()->json(['data' => $files]);
    }

    public function last()
    {
        $files = Storage::files('images');
        if (empty($files)) {
            return response()->json(['message' => 'No images found'], 404);
        }

        $lastFile = end($files);
        $file = Storage::get($lastFile);
        $type = Storage::mimeType($lastFile);

        return response($file, 200)->header('Content-Type', $type);
    }

    public function attach(FileAttachmentRequest $request)
    {
        $validated = $request->validated();
        $files = [];

        if (isset($validated['file'])) {
            $file = $validated['file'];
            $fileRecord = FileFactory::create(
                $file,
            );
            $files[] = $fileRecord;
        } else {
            foreach ($validated['files'] as $file) {
                $fileRecord = FileFactory::create(
                    $file,
                );
                $files[] = $fileRecord;
            }
        }

        return ResponseFactory::success('successful_upload', $files)->setStatusCode(201);
    }

    public function getAttachment(string $uuid)
    {
        $path = "uploads/attachment/{$uuid}";
        $file = Storage::get($path);
        if (empty($file)) {
            return ResponseFactory::error('no_images_found');
        }

        $type = Storage::mimeType($path);

        return response($file, 200)->header('Content-Type', $type);
    }
}
