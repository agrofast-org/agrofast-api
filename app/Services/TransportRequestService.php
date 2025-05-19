<?php

// app/Services/TransportRequestService.php

namespace App\Services;

use App\Models\Transport\Request;
use App\Services\Google\Contracts\DistanceMatrixClientInterface;
use App\Services\Google\Contracts\PlacesClientInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransportRequestService
{
    public function __construct(
        protected PlacesClientInterface $placesClient,
        protected DistanceMatrixClientInterface $distanceClient,
    ) {}

    public function validateTransportRequest(int $requestId): void
    {
        $request = Request::findOrFail($requestId);

        DB::transaction(function () use ($request) {
            try {
                $origin = $this->placesClient->getPlaceData($request->origin_place_id);
                $destination = $this->placesClient->getPlaceData($request->destination_place_id);
                $matrix = $this->distanceClient->getDistance($origin['formattedAddress'], $destination['formattedAddress']);

                $request->update([
                    'origin_place_name' => $origin['formattedAddress'],
                    'origin_latitude' => $origin['location']['latitude'],
                    'origin_longitude' => $origin['location']['longitude'],
                    'destination_place_name' => $destination['formattedAddress'],
                    'destination_latitude' => $destination['location']['latitude'],
                    'destination_longitude' => $destination['location']['longitude'],
                    'distance' => $matrix['distance']['value'],
                    'estimated_time' => $matrix['duration']['value'],
                    'estimated_cost' => Request::getEstimatedCost($matrix['distance']['value']),
                    'state' => Request::STATE_PAYMENT_PENDING,
                ]);
            } catch (\Throwable $e) {
                Log::warning("Request {$request->id} rejeitado: {$e->getMessage()}");
                $request->update(['state' => Request::STATE_REJECTED, 'origin_place_name' => $e->getMessage()]);
            }
        });
    }

    public function updatePaymentStatus(string $uuid): Request
    {
        $transportRequest = Request::where('uuid', $uuid)->firstOrFail();

        $client = new Client();

        try {
            $response = $client->get("https://api.mercadopago.com/v1/payments/{$transportRequest->pix_payment_id}", [
                'headers' => [
                    'Authorization' => 'Bearer '.config('services.mercadopago.access_token'),
                ],
            ]);

            $paymentData = json_decode($response->getBody(), true);

            if (isset($paymentData['status'])) {
                if ($paymentData['status'] === 'approved') {
                    $transportRequest->state = Request::STATE_APPROVED;
                } elseif ($paymentData['status'] === 'pending') {
                    $transportRequest->state = Request::STATE_PENDING;
                } else {
                    $transportRequest->state = Request::STATE_REJECTED;
                }
            } else {
                $transportRequest->state = Request::STATE_REJECTED;
            }
        } catch (\Exception $e) {
            Log::error("Error checking Mercado Pago payment status for request {$transportRequest->id}: ".$e->getMessage());
            $transportRequest->state = Request::STATE_REJECTED;
        }

        $transportRequest->state = Request::STATE_APPROVED; // or any other state based on payment status
        $transportRequest->save();

        return $transportRequest;
    }
}
