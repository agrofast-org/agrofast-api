<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request\StoreRequestRequest;
use App\Jobs\ValidateGooglePlacesJob;
use App\Models\Hr\User;
use App\Models\Transport\TransportRequest;
use App\Services\TransportRequestService;

class RequestController extends Controller
{
    public function store(StoreRequestRequest $request)
    {
        $data = $request->validated();

        $user = User::auth();

        $transportRequest = TransportRequest::create(array_merge(
            $data,
            ['user_id' => $user->id, 'state' => 'pending']
        ));

        // ValidateGooglePlacesJob::dispatch($transportRequest->id);
        $transportValidationService = new TransportRequestService();
        $transportValidationService->validateTransportRequest($transportRequest->id);

        return response()->json($transportRequest, 201);
    }
}
