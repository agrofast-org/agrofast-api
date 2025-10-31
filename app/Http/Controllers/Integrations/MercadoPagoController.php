<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Hr\PixPayment;
use App\Models\Transport\Request as TransportRequest;
use App\Services\MercadoPago\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Summary of webhook.
     *
     * TODO: documentar melhor o funcionamento deste webhook
     *
     * @return JsonResponse
     */
    public function webhook(Request $request, PaymentService $paymentService)
    {
        $payload = $request->all();

        Log::info('[MP] Webhook recebido', $payload);

        if (
            empty($payload['type'])
            || empty($payload['data']['id'])
            || $payload['type'] !== 'payment'
        ) {
            return response()->json(['ignored' => true], 200);
        }

        $paymentId = (int) $payload['data']['id'];

        try {
            $mpPayment = $paymentService->getPaymentStatus($paymentId);

            $pixPayment = PixPayment::where('payment_id', $paymentId)->first();

            if ($pixPayment) {
                $pixPayment->update([
                    'status' => $mpPayment['status'],
                    'status_detail' => $mpPayment['status_detail'],
                    'raw_response' => json_encode($mpPayment['raw']),
                ]);

                $transportRequest = TransportRequest::where('payment_id', $pixPayment->id)->first();
                if ($transportRequest) {
                    if ($mpPayment['status'] === 'approved') {
                        $transportRequest->state = TransportRequest::STATE_APPROVED;
                    } elseif (in_array($mpPayment['status'], ['cancelled', 'rejected', 'refunded'], true)) {
                        // $transportRequest->state = TransportRequest::STATE_REJECTED;
                    }
                }

                Log::info("[MP] Pagamento {$paymentId} atualizado: {$mpPayment['status']}");
            } else {
                Log::warning("[MP] Pagamento não encontrado: {$paymentId}");
            }
        } catch (\Throwable $e) {
            Log::error('[MP] Erro ao processar webhook: '.$e->getMessage(), [
                'payment_id' => $paymentId,
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'processing_failed'], 500);
        }

        return response()->json(['status' => 'processed'], 200);
    }
}
