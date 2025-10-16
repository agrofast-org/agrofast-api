<?php

// app/Services/TransportRequestService.php

namespace App\Services;

use App\Models\System\ErrorLog;
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
            $data = [];

            try {
                $origin = $this->placesClient->getPlaceData($request->origin_place_id);
                $data['origin_place_name'] = $origin['formattedAddress'];
                $data['origin_latitude'] = $origin['location']['latitude'];
                $data['origin_longitude'] = $origin['location']['longitude'];

                $destination = $this->placesClient->getPlaceData($request->destination_place_id);
                $data['destination_place_name'] = $destination['formattedAddress'];
                $data['destination_latitude'] = $destination['location']['latitude'];
                $data['destination_longitude'] = $destination['location']['longitude'];

                $matrix = $this->distanceClient->getDistance($origin['formattedAddress'], $destination['formattedAddress']);
                $data['distance'] = $matrix['distance']['value'];
                $data['estimated_time'] = $matrix['duration']['value'];
                $data['estimated_cost'] = Request::getEstimatedCost($matrix['distance']['value']);
                $data['state'] = Request::STATE_PAYMENT_PENDING;
            } catch (\Throwable $th) {
                $this->rejected($request, $data, $th);
            }

            $request->update($data);
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

    private function rejected(Request $request, array $data, \Throwable $throwable): void
    {
        ErrorLog::create([
            'url' => 'N/A',
            'error_message' => $throwable->getMessage(),
            'stack_trace' => $throwable->getTraceAsString(),
            'request_data' => $data,
        ]);
        $data['state'] = Request::STATE_REJECTED;
        $request->update($data);
    }
}
