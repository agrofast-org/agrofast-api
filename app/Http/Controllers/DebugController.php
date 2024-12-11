<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    public function showEnvironment(Request $request)
    {
        return response()->json([
            'message' => ['ping' => 'pong'],
            'data'    => ['request' => $GLOBALS],
            'request' => [
                'request_method' => $request->method(),
                'params'         => $request->route()->parameters(),
                'body'           => $request->all(),
                'query'          => $request->query(),
            ],
            'raw_data' => $request->getContent(),
        ]);
    }

    public function showNestedParams(Request $request)
    {
        return response()->json([
            'request' => [
                'params' => $request->route()->parameters(),
                'query'  => $request->query(),
            ],
        ]);
    }

    public function getEnvironmentInstructions()
    {
        return response()->json([
            'message' => [
                'instruction' => 'There is none yet.',
            ],
        ]);
    }

    public function getEnvironmentVariable($variable)
    {
        return response()->json([
            'message' => 'This functionality will not return values.',
            'data'    => [
                'requested_var' => $variable,

            ],
        ]);
    }

    public function mapProjectFiles()
    {
        $projectRoot = base_path();
        $files = $this->readDirectory($projectRoot);

        return response()->json([
            'data' => $files,
        ]);
    }

    public function getFileContent(Request $request)
    {
        $filePath = $request->query('path');
        $fullPath = base_path($filePath);

        if (File::exists($fullPath)) {
            return response()->json([
                'data' => File::get($fullPath),
            ]);
        }

        return response()->json([
            'message' => 'File not found',
        ], 404);
    }

    public function showBody(Request $request)
    {
        return response()->json([
            'data' => $request->all(),
        ]);
    }

    public function getLastError()
    {
        $lastError = Log::getLogs()->last();

        if ($lastError) {
            return response()->json(['data' => $lastError]);
        }

        return response()->json(['data' => null]);
    }

    private function readDirectory($directory)
    {
        $items = [];
        foreach (scandir($directory) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$item;
            $items[] = [
                'name' => $item,
                'type' => is_dir($path) ? 'directory' : 'file',
                'path' => $path,
            ];
        }

        return $items;
    }
}
