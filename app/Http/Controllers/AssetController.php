<?php

namespace App\Http\Controllers;

use App\Factories\FileFactory;
use App\Factories\ResponseFactory;
use App\Http\Requests\User\FileAttachmentRequest;
use App\Models\File\File;
use App\Models\Hr\User;
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
        $uuids = [];

        if (isset($validated['file'])) {
            $file = $validated['file'];
            $path = Storage::putFile('images', $file);
            $fileRecord = FileFactory::create(
                $file,
                $path,
            );
            $uuids[] = $fileRecord->uuid;
            $paths[] = $fileRecord->path;
        } else {
            $files = $validated['files'];
            $paths = [];
            foreach ($files as $file) {
                $path = Storage::putFile('images', $file);
                $fileRecord = FileFactory::create(
                    $file,
                    $path,
                );
                $uuids[] = $fileRecord->uuid;
                $paths[] = $fileRecord->path;
            }
        }

        return ResponseFactory::success('successful_upload', [
            'uuids' => $uuids,
            '' => $paths,
        ])->setStatusCode(201);
    }

    public function getAttachment(string $uuid)
    {

    }
}
