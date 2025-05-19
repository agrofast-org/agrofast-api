<?php

namespace App\Services\MercadoPago;

use App\Models\Hr\PixPayment;
use App\Models\Hr\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class PaymentService
{
    protected string $baseUrl;
    protected string $accessToken;

    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
        $this->baseUrl = config('services.mercadopago.base_url', 'https://api.mercadopago.com');
        $this->accessToken = config('services.mercadopago.access_token');
    }

    public function makePayment(string $amount, User $user, $paymentMethod = 'pix')
    {
        if (!is_numeric($amount) || $amount <= 0) {
            throw new \InvalidArgumentException('Amount must be a positive number.');
        }
        if (empty($user->email)) {
            throw new \InvalidArgumentException('User email is required for payment.');
        }
        // safety mesures lol
        $amount = '1.00';

        $paymentUuid = Str::uuid()->toString();

        $client = new PaymentClient();
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(['X-Idempotency-Key: '.$paymentUuid]);

        $paymentResponse = $client->create([
            'transaction_amount' => (float) $amount,
            'payment_method_id' => $paymentMethod,
            'payer' => [
                'email' => $user->email,
            ],
        ], $request_options);

        return PixPayment::create([
            'uuid' => $paymentUuid,
            'payment_id' => $paymentResponse->id,
            'status' => $paymentResponse->status,
            'status_detail' => $paymentResponse->status_detail,
            'transaction_amount' => $paymentResponse->transaction_amount,
            'external_reference' => $paymentResponse->external_reference,
            'date_created' => $paymentResponse->date_created,
            'date_approved' => $paymentResponse->date_approved,
            'date_last_updated' => $paymentResponse->date_last_updated,
            'date_of_expiration' => $paymentResponse->date_of_expiration,
            'qr_code_base64' => $paymentResponse->point_of_interaction->transaction_data->qr_code_base64,
            'qr_code' => $paymentResponse->point_of_interaction->transaction_data->qr_code,
            'ticket_url' => $paymentResponse->point_of_interaction->transaction_data->ticket_url,
        ]);
    }

    /**
     * Consulta o status de um pagamento por ID usando a API REST do Mercado Pago.
     *
     * @return array{status: string, status_detail: null|string, raw: array}
     *
     * @throws \RuntimeException
     */
    public function getPaymentStatus(int $paymentId): array
    {
        $url = "{$this->baseUrl}/v1/payments/{$paymentId}";

        $response = Http::withToken($this->accessToken)
            ->timeout(5)
            ->get($url)
        ;

        if ($response->failed()) {
            throw new \RuntimeException("Falha ao consultar pagamento MP: HTTP {$response->status()}");
        }

        $data = $response->json();

        return [
            'status' => $data['status'] ?? 'unknown',
            'status_detail' => $data['status_detail'] ?? null,
            'raw' => $data,
        ];
    }
}
