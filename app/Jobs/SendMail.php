<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class SendMail implements ShouldQueue
{
    use Queueable;

    /**
     * @param class-string<Mailable> $mailableClass
     * @param mixed                  $tries
     */
    public function __construct(
        public array|string $to,
        public string $mailableClass,
        public array $data = [],
        public $tries = 1
    ) {}

    public function handle(): void
    {
        $mailable = new $this->mailableClass($this->data);

        Mail::to($this->to)->send($mailable);
    }
}
