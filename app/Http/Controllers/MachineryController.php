<?php

namespace App\Http\Controllers;

use App\Http\Requests\Machinery\StoreMachineryRequest;
use App\Http\Requests\Machinery\UpdateMachineryRequest;
use App\Models\File\File;
use App\Models\Hr\User;
use App\Models\Transport\Machinery;
use Illuminate\Support\Str;

class MachineryController extends Controller
{
    public function index()
    {
        $machineries = Machinery::where('user_id', User::auth()->id)
            ->where('active', true)
            ->orderBy('created_at', 'desc')
        ;

        return response()->json($machineries->get()->toArray(), 200);
    }

    public function store(StoreMachineryRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = User::auth()->id;

        $data['uuid'] = Str::uuid();

        $machineries = Machinery::create($data);

        if ($data['pictures']) {
            $files = File::whereIn('uuid', $data['pictures'])
                ->where('uploaded_by', User::auth()->id)
                ->where('active', true)
                ->get()
            ;
            foreach ($files as $file) {
                $machineries->addPicture($file->id);
            }
            File::markAsAttached($data['pictures']);
        }

        return response()->json($machineries, 201);
    }

    public function show(string $uuid)
    {
        $machinery = Machinery::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$machinery) {
            return response()->json(['message' => 'Machinery not found'], 404);
        }

        return response()->json($machinery, 200);
    }

    public function update(UpdateMachineryRequest $request, string $uuid)
    {
        $machinery = Machinery::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$machinery) {
            return response()->json(['message' => 'Machinery not found'], 404);
        }

        $data = $request->validated();
        $machinery->update($data);

        return response()->json($machinery, 200);
    }

    public function disable(string $uuid)
    {
        $machinery = Machinery::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$machinery) {
            return response()->json(['message' => 'Machinery not found'], 404);
        }

        $machinery->update(['active' => false, 'inactivated_at' => now()]);

        return response()->json(['message' => 'Machinery deleted successfully'], 200);
    }
}
