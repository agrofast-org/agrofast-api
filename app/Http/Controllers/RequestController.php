<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request\StoreRequestRequest;
use App\Models\Hr\PixPayment;
use App\Models\Hr\User;
use App\Models\Transport\Machinery;
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
    ) {}

    public function index(): JsonResponse
    {
        $user = User::auth();
        $requests = TransportRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
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
            ->where('state', '!=', TransportRequest::STATE_REJECTED)
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

        if (!empty($transportRequest) && $transportRequest->state === TransportRequest::STATE_PAYMENT_PENDING) {
            $paymentService = new PaymentService();
            $pixPayment = $paymentService->makePayment(
                $transportRequest->estimated_cost,
                $user,
            );

            $transportRequest->update([
                'payment_id' => $pixPayment->id,
                'state' => TransportRequest::STATE_PAYMENT_PENDING,
            ]);
        }

        return response()->json($transportRequest, 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $transportRequest = TransportRequest::where('uuid', $uuid)
            ->firstOrFail()
        ;

        $pixPayment = PixPayment::where('id', $transportRequest->payment_id)
            ->first()
        ;

        $data = $transportRequest->toArray();
        $data['payment'] = $pixPayment;

        return response()->json($data, 200);
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
}
