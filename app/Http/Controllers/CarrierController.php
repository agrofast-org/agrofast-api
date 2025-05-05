<?php

namespace App\Http\Controllers;

use App\Factories\ResponseFactory;
use App\Http\Requests\Carrier\StoreCarrierRequest;
use App\Http\Requests\Carrier\UpdateCarrierRequest;
use App\Models\Hr\User;
use App\Models\Transport\Carrier;

class CarrierController extends Controller
{
    public function index()
    {
        $carrier = Carrier::where('user_id', User::auth()->id)
            ->where('active', true)
            ->orderBy('created_at', 'desc')
        ;

        return ResponseFactory::success(
            'carrier_list',
            $carrier
        );
    }

    public function store(StoreCarrierRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = User::auth()->id;

        Carrier::create($data);

        $carrier = Carrier::where('user_id', User::auth()->id)
            ->orderBy('created_at', 'desc')
        ;

        return ResponseFactory::success(
            'carrier_created',
            $carrier
        );
    }

    public function show(string $uuid)
    {
        $carrier = Carrier::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$carrier) {
            return ResponseFactory::error(
                'carrier_not_found',
                null,
                null,
                404
            );
        }

        return ResponseFactory::success(
            'carrier',
            $carrier
        );
    }

    public function update(UpdateCarrierRequest $request, string $uuid)
    {
        $carrier = Carrier::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$carrier) {
            return ResponseFactory::error(
                'carrier_not_found',
                null,
                null,
                404
            );
        }

        $data = $request->validated();
        $carrier->update($data);

        return ResponseFactory::success(
            'Carrier_updated',
            $carrier
        );
    }

    public function destroy(string $uuid)
    {
        $carrier = Carrier::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$carrier) {
            return ResponseFactory::error(
                'carrier_not_found',
                null,
                null,
                404
            );
        }

        $carrier->update(['active' => false, 'inactivated_at' => now()]);

        return ResponseFactory::success(
            'carrier_deleted',
            $carrier
        );
    }
}
