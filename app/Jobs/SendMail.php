<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class SendMail implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public array|string $to;
    public string $mailableClass;
    public array $data;
    public string $locale;
    public int $tries;

    /**
     * @param class-string<Mailable> $mailableClass
     */
    public function __construct(
        array|string $to,
        string $mailableClass,
        array $data = [],
        int $tries = 1
    ) {
        $this->to = $to;
        $this->mailableClass = $mailableClass;
        $this->data = $data;
        $this->tries = $tries;
        $this->locale = App::getLocale();
    }

    public function handle(): void
    {
        $mailable = (new $this->mailableClass($this->data))
            ->locale($this->locale)
        ;

        Mail::to($this->to)
            ->locale($this->locale)
            ->send($mailable)
        ;
    }
}
