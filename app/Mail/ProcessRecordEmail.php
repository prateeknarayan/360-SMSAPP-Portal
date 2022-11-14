<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProcessRecordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $processRecord;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($processRecord)
    {
        $this->processRecord = $processRecord;
    }

    /**
     * Build the message.
     *
     * @return $this
    */
    public function build()
    {
        return $this->from('support@tts.com', 'AdminPanel')->subject('Process Record')->view('emails.processrecord');
    }
}
