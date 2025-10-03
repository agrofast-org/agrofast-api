<?php

namespace App\Http\Controllers;

use App\Http\Requests\Carrier\StoreCarrierRequest;
use App\Http\Requests\Carrier\UpdateCarrierRequest;
use App\Models\File\File;
use App\Models\Hr\User;
use App\Models\Transport\Carrier;
use Illuminate\Support\Str;

class CarrierController extends Controller
{
    public function index()
    {
        $carrier = Carrier::where('user_id', User::auth()->id)
            ->where('active', true)
            ->orderBy('created_at', 'desc')
        ;

        return response()->json($carrier->get()->toArray(), 200);
    }

    public function store(StoreCarrierRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = User::auth()->id;

        $data['uuid'] = Str::uuid();

        $carrier = Carrier::create($data);

        if ($data['documents']) {
            $files = File::whereIn('uuid', $data['documents'])
                ->where('uploaded_by', User::auth()->id)
                ->where('active', true)
                ->get()
            ;
            foreach ($files as $file) {
                $carrier->addDocument($file->id);
            }
        }

        if ($data['pictures']) {
            $files = File::whereIn('uuid', $data['pictures'])
                ->where('uploaded_by', User::auth()->id)
                ->where('active', true)
                ->get()
            ;
            foreach ($files as $file) {
                $carrier->addPicture($file->id);
            }
        }

        return response()->json($carrier, 201);
    }

    public function show(string $uuid)
    {
        $carrier = Carrier::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$carrier) {
            return response()->json(['message' => 'Carrier not found'], 404);
        }

        return response()->json($carrier, 200);
    }

    public function update(UpdateCarrierRequest $request, string $uuid)
    {
        $carrier = Carrier::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$carrier) {
            return response()->json(['message' => 'Carrier not found'], 404);
        }

        $data = $request->validated();
        $carrier->update($data);

        return response()->json(['message' => 'Carrier updated successfully', 'data' => $carrier], 200);
    }

    public function disable(string $uuid)
    {
        $carrier = Carrier::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$carrier) {
            return response()->json(['message' => 'Carrier not found'], 404);
        }

        $carrier->update(['active' => false, 'inactivated_at' => now()]);

        return response()->json(['message' => 'Carrier disabled successfully'], 200);
    }
}
