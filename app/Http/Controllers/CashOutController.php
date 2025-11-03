<?php

namespace App\Http\Controllers;

use App\Exception\InvalidFormException;
use App\Http\Requests\CashOut\StoreCashOutRequest;
use App\Models\Hr\CashOut;
use App\Models\Hr\User;
use App\Models\Transport\Offer;
use App\Models\Transport\Request as TransportRequest;
use Illuminate\Support\Str;

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

        $availableFunds = Offer::query()->where('user_id', $user->id)
            ->where('state', Offer::STATE_COMPLETED)
            ->whereHas('request', function ($query) {
                $query->where('state', TransportRequest::STATE_COMPLETED);
            })
            ->sum('gain');

        $totalRequested = $user->cashOuts()->sum('amount');

        $fundsLeft = $availableFunds - $totalRequested;

        if ($data['amount'] > $fundsLeft) {
            throw new InvalidFormException('Insufficient funds for this cash out request.', [
                'amount' => "Saldo insuficiente.",
            ]);
        }

        $cashOut = CashOut::create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'status' => 'pending',
        ]);

        return response()->json($cashOut, 201);
    }

    public function funds()
    {
        $user = User::auth();
        $totalGains = Offer::query()->where('user_id', $user->id)
            ->where('state', Offer::STATE_COMPLETED)
            ->whereHas('request', function ($query) {
                $query->where('state', TransportRequest::STATE_COMPLETED);
            })
            ->sum('gain');

        $totalCashedOut = $user->cashOuts()->where('status', 'approved')->sum('amount');

        $available = $totalGains - $totalCashedOut;

        return response()->json($available);
    }
}
