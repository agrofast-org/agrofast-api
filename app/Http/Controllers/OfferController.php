<?php

namespace App\Http\Controllers;

use App\Http\Requests\Offer\StoreOfferRequest;
use App\Models\Hr\User;
use App\Models\Transport\Offer;
use App\Models\Transport\Request as TransportRequest;
use App\Services\Chat\ChatService;
use App\Services\MercadoPago\PaymentService;
use App\Services\Transport\OfferService;
use Illuminate\Http\JsonResponse;

class OfferController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected ChatService $chatService,
        protected OfferService $offerService
    ) {}

    public function index(): JsonResponse
    {
        $user = User::auth();
        // $offers = Offer::with(['request', 'carrier'])->where('user_id', $user->id)->get();
        $offers = Offer::where('user_id', $user->id)->get();

        return response()->json($offers);
    }

    /**
     * Summary of show.
     *
     * @param string $uuid offer uuid
     */
    public function show(string $uuid): JsonResponse
    {
        $user = User::auth();
        $offer = Offer::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first()
        ;

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        return response()->json($offer);
    }

    /**
     * Summary of received.
     *
     * @param mixed $uuid request uuid
     *
     * @return JsonResponse
     */
    public function received($uuid)
    {
        $user = User::auth();

        $transportRequest = TransportRequest::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first()
        ;
        if (!$transportRequest) {
            return response()->json(['message' => 'Transport request not found'], 404);
        }
        $offers = Offer::where('request_id', $transportRequest->id)
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->get()
        ;

        return response()->json($offers);
    }

    public function store(StoreOfferRequest $request): JsonResponse
    {
        [
            'validated' => $validated,

            'transporter' => $transporter,
            'requestant' => $requestant,

            'transportRequest' => $transportRequest,
            // 'carrier' => $carrier,
            'offer' => $offer,
        ] = $this->offerService->makeOffer($request);

        if ($this->offerService->getHasMinimumPrice($offer, $transportRequest)) {
            [
                'chat' => $chat,
            ] = $this->offerService->acceptOffer($offer, $transportRequest, $requestant, $transporter, $validated['message']);

            return response()->json([
                'chat_uuid' => $chat->uuid,
            ], 201);
        }

        return response()->json($offer, 201);
    }

    /**
     * Summary of accept.
     *
     * @param mixed $uuid offer uuid
     *
     * @return JsonResponse
     */
    public function accept($uuid)
    {
        [
            'offer' => $offer,
            'transportRequest' => $transportRequest,
            'requestant' => $requestant,
            'transporter' => $transporter,
        ] = $this->offerService->validateOfferAcception($uuid);

        $this->offerService->acceptOffer($offer, $transportRequest, $requestant, $transporter);

        return response()->json(['message' => 'Offer accepted'], 200);
    }

    /**
     * Summary of update.
     *
     * @param mixed $uuid offer uuid
     */
    public function delete($uuid): JsonResponse
    {
        $user = User::auth();
        $offer = Offer::where('uuid', $uuid)->where('user_id', $user->id)->first();

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        $offer->update(['state' => Offer::STATE_CANCELED, 'active' => false]);

        return response()->json(['message' => 'Offer cancelled'], 200);
    }
}
