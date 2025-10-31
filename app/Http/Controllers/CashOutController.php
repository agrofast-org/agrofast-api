<?php

namespace App\Http\Controllers;

use App\Exception\InvalidFormException;
use App\Http\Requests\Machinery\StoreCashOutRequest;
use App\Models\Hr\User;
use App\Models\Transport\Offer;
use App\Models\Transport\Request as TransportRequest;

class CashOutController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $user = User::auth();

        return response()->json($user->cashOuts()->get());
    }

    public function store(StoreCashOutRequest $request)
    {
        $user = User::auth();

        $data = $request->validated();

        $availableFunds = Offer::where('user_id', $user->id)
            ->where('state', Offer::STATE_COMPLETED)
            ->whereHas('request', function ($query) {
                $query->where('state', TransportRequest::STATE_COMPLETED);
            })
            ->sum('gain');

        $totalRequested = $user->cashOuts()->sum('amount');

        $fundsLeft = $availableFunds - $totalRequested;

        if ($data['amount'] > $fundsLeft) {
            throw new InvalidFormException('Insufficient funds for this cash out request.', [
                'available_funds' => $availableFunds,
            ]);
        }

        $cashOut = $user->cashOuts()->create([
            'amount' => $data['amount'],
            'state' => 'pending',
        ]);

        return response()->json($cashOut, 201);
    }

    public function funds()
    {
        $user = User::auth();
        $totalGains = Offer::where('user_id', $user->id)
            ->where('state', Offer::STATE_COMPLETED)
            ->whereHas('request', function ($query) {
                $query->where('state', TransportRequest::STATE_COMPLETED);
            })
            ->sum('gain');

        $totalCashedOut = $user->cashOuts()->where('state', 'approved')->sum('amount');

        $available = $totalGains - $totalCashedOut;

        return response()->json($available);
    }
}
