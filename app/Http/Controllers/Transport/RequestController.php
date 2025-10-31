<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request\StoreRequestRequest;
use App\Models\Hr\PixPayment;
use App\Models\Hr\User;
use App\Models\Transport\Machinery;
use App\Models\Transport\Offer;
use App\Models\Transport\Request as TransportRequest;
use App\Services\MercadoPago\PaymentService;
use App\Services\TransportRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class RequestController extends Controller
{
    public function __construct(
        protected TransportRequestService $transportService,
        protected PaymentService $pixClient,
    ) {
    }

    public function index(): JsonResponse
    {
        $user = User::auth();
        $requests = TransportRequest::with(['pix_payment'])
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->get()
        ;

        return response()->json($requests, 200);
    }

    public function store(StoreRequestRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::auth();

        $machinery = Machinery::where('uuid', $data['machine_uuid'])
            ->where('user_id', $user->id)
            ->firstOrFail()
        ;

        if (!$machinery->active) {
            return response()->json(['message' => 'Machinery not found'], 404);
        }

        $request = TransportRequest::where('user_id', $user->id)
            ->where('machine_id', $machinery->id)
            ->where('active', true)
            ->whereIn('state', [
                TransportRequest::STATE_PENDING,
                TransportRequest::STATE_WAITING_FOR_OFFER,
                TransportRequest::STATE_PAYMENT_PENDING,
                TransportRequest::STATE_APPROVED,
                TransportRequest::STATE_IN_PROGRESS,
                TransportRequest::STATE_REJECTED,
            ], 'and', true)
            ->first()
        ;

        if (!empty($request)) {
            return response()->json(['message' => 'Request already exists'], 409);
        }

        $data['uuid'] = Str::uuid()->toString();
        $data['user_id'] = $user->id;
        $data['machine_id'] = $machinery->id;
        $data['state'] = TransportRequest::STATE_PENDING;

        $transportRequest = TransportRequest::create($data);

        $this->transportService->validateTransportRequest($transportRequest->id);

        $transportRequest = TransportRequest::where('id', $transportRequest->id)
            ->first()
        ;

        return response()->json($transportRequest, 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $transportRequest = TransportRequest::with(['machine', 'user'])
            ->where('uuid', $uuid)
            ->firstOrFail()
        ;

        $pixPayment = PixPayment::where('id', $transportRequest->payment_id)
            ->first()
        ;

        $data = $transportRequest->toArray();
        $data['payment'] = $pixPayment;

        return response()->json($data, 200);
    }

    public function offers(string $uuid)
    {
        $user = User::auth();
        $transportRequest = TransportRequest::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail()
        ;

        $offers = Offer::where('request_id', $transportRequest->id)
            ->join('hr.user', 'hr.user.id', '=', 'offer.user_id')
            ->select('offer.*', 'hr.user.name as user_name')
            ->with(['carrier'])
            ->orderBy('created_at', 'desc')
            ->get()
        ;

        return response()->json($offers, 200);
    }

    public function updatePaymentStatus(string $uuid): JsonResponse
    {
        $transportRequest = TransportRequest::where('uuid', $uuid)->firstOrFail();

        if ($transportRequest->state === TransportRequest::STATE_PAYMENT_PENDING) {
            $pix = PixPayment::where('id', $transportRequest->payment_id)
                ->first()
            ;

            if ($pix) {
                $statusData = $this->pixClient->getPaymentStatus($pix->payment_id);

                $pix->update([
                    'status' => $statusData['status'],
                    'status_detail' => $statusData['status_detail'],
                    'date_last_updated' => now(),
                ]);

                if ($statusData['status'] === 'approved') {
                    $transportRequest->state = TransportRequest::STATE_APPROVED;
                } elseif (in_array($statusData['status'], ['cancelled', 'rejected', 'refunded'], true)) {
                    $transportRequest->state = TransportRequest::STATE_REJECTED;
                }

                $transportRequest->save();
            }
        }

        return response()->json([
            'message' => 'request_payment_status_updated',
            'data' => $transportRequest,
        ]);
    }

    public function complete($uuid)
    {
        $user = User::auth();
        $transportRequest = TransportRequest::where('uuid', $uuid)->where('user_id', $user->id)->first();

        if (!$transportRequest) {
            return response()->json(['message' => 'Chamado não encontrado.'], 404);
        }

        if ($transportRequest->state !== TransportRequest::STATE_IN_PROGRESS) {
            return response()->json(['message' => 'Chamado não está em andamento.'], 400);
        }

        $offer = Offer::where('request_id', $transportRequest->id)
            ->where('state', Offer::STATE_IN_PROGRESS)
            ->first()
        ;

        if ($offer->state !== Offer::STATE_COMPLETED) {
            return response()->json(['message' => 'Aguarde até que o transportador marque a oferta como concluída.'], 404);
        }

        [
            "raw" => $payment
        ] = $this->pixClient->getPaymentStatus($transportRequest->payment_id);

        $offer->update([
            'gain' => $payment->transaction_amount * 0.95,
        ]);
        $transportRequest->update(['state' => TransportRequest::STATE_COMPLETED]);

        return response()->json(['message' => 'Chamado concluído.'], 200);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $user = User::auth();
        $transportRequest = TransportRequest::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail()
        ;

        if (in_array($transportRequest->state, [
            TransportRequest::STATE_IN_PROGRESS,
            TransportRequest::STATE_COMPLETED,
            TransportRequest::STATE_CANCELED,
        ], true)) {
            return response()->json(['message' => 'Cannot cancel request in this state'], 400);
        }

        $transportRequest->update([
            'state' => TransportRequest::STATE_CANCELED,
            'active' => false,
            'inactivated_at' => now(),
        ]);

        return response()->json(['message' => 'Request canceled successfully'], 200);
    }

    public function listRequestsForOffer(): JsonResponse
    {
        $requests = TransportRequest::with(['machine', 'user'])
            ->where('state', TransportRequest::STATE_WAITING_FOR_OFFER)
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->get()
        ;

        return response()->json($requests, 200);
    }
}
