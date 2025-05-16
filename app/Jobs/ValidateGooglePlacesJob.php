<?php
namespace App\Jobs;

use App\Models\Transport\TransportRequest;
use App\Services\TransportRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ValidateGooglePlacesJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    protected int $requestId;

    public function __construct(int $requestId)
    {
        $this->requestId = $requestId;
    }

    public function handle()
    {
        $transportValidationService = new TransportRequestService();
        $transportValidationService->validateTransportRequest($this->requestId);
    }
}
