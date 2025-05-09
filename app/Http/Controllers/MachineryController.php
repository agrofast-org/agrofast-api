<?php

namespace App\Http\Controllers;

use App\Factories\ResponseFactory;
use App\Http\Requests\Machinery\StoreMachineryRequest;
use App\Http\Requests\Machinery\UpdateMachineryRequest;
use App\Models\Hr\User;
use App\Models\Transport\Machinery;

class MachineryController extends Controller
{
    public function index()
    {
        $machineries = Machinery::where('user_id', User::auth()->id)
            ->where('active', true)
            ->orderBy('created_at', 'desc')
        ;

        return ResponseFactory::success(
            'machinery_list',
            $machineries->get()->toArray(),
        );
    }

    public function store(StoreMachineryRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = User::auth()->id;

        $machineries = Machinery::create($data);

        return ResponseFactory::success(
            'machineries_created',
            $machineries
        );
    }

    public function show(string $uuid)
    {
        $machinery = Machinery::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$machinery) {
            return ResponseFactory::error(
                'machinery_not_found',
                null,
                null,
                404
            );
        }

        return ResponseFactory::success(
            'machinery',
            $machinery
        );
    }

    public function update(UpdateMachineryRequest $request, string $uuid)
    {
        $machinery = Machinery::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$machinery) {
            return ResponseFactory::error(
                'machinery_not_found',
                null,
                null,
                404
            );
        }

        $data = $request->validated();
        $machinery->update($data);

        return ResponseFactory::success(
            'machinery_updated',
            $machinery
        );
    }

    public function disable(string $uuid)
    {
        $machinery = Machinery::where('uuid', $uuid)
            ->where('user_id', User::auth()->id)
            ->where('active', true)
            ->firstOrFail()
        ;

        if (!$machinery) {
            return ResponseFactory::error(
                'machinery_not_found',
                null,
                null,
                404
            );
        }

        $machinery->update(['active' => false, 'inactivated_at' => now()]);

        return ResponseFactory::success(
            'machinery_deleted',
            $machinery
        );
    }
}
