<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\System\SystemMessage;

class SystemMessageNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $messageData;

    public function __construct(SystemMessage $message)
    {
        $this->messageData = $message;
    }

    public function build()
    {
        return $this->subject($this->messageData->title)
            ->view('emails.system_notification')
            ->with(['messageData' => $this->messageData]);
    }
}
