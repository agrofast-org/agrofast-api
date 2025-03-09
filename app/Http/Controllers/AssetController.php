<?php

namespace App\Http\Controllers;

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
}
