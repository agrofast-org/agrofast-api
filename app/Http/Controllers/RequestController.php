<?php

namespace App\Http\Controllers;

use App\Factories\ResponseFactory;
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

        return ResponseFactory::success('requests_found', $requests);
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
            return ResponseFactory::error('machinery_not_found', [
                'machine_uuid' => __('validation.custom.request.machinery_not_found'),
            ]);
        }

        $request = TransportRequest::where('user_id', $user->id)
            ->where('active', true)
            ->first()
        ;

        if (!empty($request)) {
            return ResponseFactory::error('request_already_exists', [
                'machine_uuid' => __('validation.custom.request.request_already_exists'),
            ]);
        }

        $data['uuid'] = Str::uuid()->toString();
        $data['user_id'] = $user->id;
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

        return ResponseFactory::success('request_created', $transportRequest);
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

        return ResponseFactory::success('request_found', $data);
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
