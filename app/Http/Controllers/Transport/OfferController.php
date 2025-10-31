<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Offer\StoreOfferRequest;
use App\Jobs\SendMail;
use App\Mail\Offer\OfferAcceptedMail;
use App\Mail\Offer\OfferCanceledMail;
use App\Mail\Offer\OfferInstantAcceptedMail;
use App\Mail\Offer\OfferReceivedAcceptedMail;
use App\Mail\Offer\OfferReceivedMail;
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
        $offers = Offer::with(['request', 'carrier'])
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->get()
        ;

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

            SendMail::dispatch($requestant->email, OfferReceivedAcceptedMail::class, [
                'user_id' => $requestant->id,
                'request_id' => $transportRequest->id,
                'offer_id' => $offer->id,
            ]);

            SendMail::dispatch($transporter->email, OfferInstantAcceptedMail::class, [
                'user_id' => $transporter->id,
                'request_id' => $transportRequest->id,
                'offer_id' => $offer->id,
            ]);

            return response()->json([
                'chat_uuid' => $chat->uuid,
            ], 201);
        }

        SendMail::dispatch($requestant->email, OfferReceivedMail::class, [
            'user_id' => $requestant->id,
            'request_id' => $transportRequest->id,
            'offer_id' => $offer->id,
        ]);

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

        SendMail::dispatch($requestant->email, OfferReceivedMail::class, [
            'user_id' => $requestant->id,
            'request_id' => $transportRequest->id,
            'offer_id' => $offer->id,
        ]);

        SendMail::dispatch($transporter->email, OfferAcceptedMail::class, [
            'user_id' => $transporter->id,
            'request_id' => $transportRequest->id,
            'offer_id' => $offer->id,
        ]);

        return response()->json(['message' => 'Offer accepted'], 200);
    }

    public function start($uuid)
    {
        $user = User::auth();
        $offer = Offer::where('uuid', $uuid)->where('user_id', $user->id)->first();

        if (!$offer) {
            return response()->json(['message' => 'Oferta não encontrada'], 404);
        }

        $hasOthersInProgress = Offer::where('request_id', $offer->request_id)
            ->where('state', Offer::STATE_IN_PROGRESS)
            ->where('id', '!=', $offer->id)
            ->exists()
        ;

        if ($hasOthersInProgress) {
            return response()->json(['message' => 'Outra oferta já está em andamento para esta solicitação'], 400);
        }

        $request = TransportRequest::find($offer->request_id);
        if ($request->state !== TransportRequest::STATE_APPROVED) {
            return response()->json(['message' => 'A solicitação associada não está aprovada'], 400);
        }

        $request->update(['state' => TransportRequest::STATE_IN_PROGRESS]);
        $request->touch();

        $offer->update(['state' => Offer::STATE_IN_PROGRESS]);
        $offer->touch();

        return response()->json(['message' => 'Oferta iniciada'], 200);
    }

    public function complete($uuid)
    {
        $user = User::auth();
        $offer = Offer::where('uuid', $uuid)->where('user_id', $user->id)->first();

        if (!$offer) {
            return response()->json(['message' => 'Oferta não encontrada'], 404);
        }

        if ($offer->state !== Offer::STATE_IN_PROGRESS) {
            return response()->json(['message' => 'Oferta não está em andamento'], 400);
        }

        $offer->update(['state' => Offer::STATE_COMPLETED]);

        return response()->json(['message' => 'Oferta concluída'], 200);
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

        SendMail::dispatch($user->email, OfferCanceledMail::class, [
            'user_id' => $user->id,
            'request_id' => $offer->request_id,
            'offer_id' => $offer->id,
        ]);

        return response()->json(['message' => 'Offer cancelled'], 200);
    }
}
