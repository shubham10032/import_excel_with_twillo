<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twilio\Rest\Client;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phoneNo;
    protected $message;

    /**
     * Create a new job instance.
     *
     * @param string $phoneNo
     * @param string $message
     */
    public function __construct($phoneNo, $message)
    {
        $this->phoneNo = $phoneNo;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle($phoneNo, $message)
    {
        $sid = 'AC46aa732916b049793ef08399ab715d7a';
        $token = '891291a4493c5ae9ca362488c3612f03';
        $twilioNumber = '+16164492026';

        // Initialize Twilio client
        $twilio = new Client($sid, $token);
        $formattedPhoneNumber = $this->formatPhoneNumber($phoneNo);
        // Send message
        $twilio->messages->create(
            $formattedPhoneNumber,
            [
                'from' => $twilioNumber, // Your Twilio phone number
                'body' => $message // Message content
            ]
        );
    }
    private function formatPhoneNumber($phoneNo)
    {   
        $phoneNo = preg_replace('/[^0-9]/', '', $phoneNo);

        if (substr($phoneNo, 0, 1) === '0') {
            $phoneNo = substr($phoneNo, 1);
        }

        // Prepend the country code '91' for India
        $formattedPhoneNumber = '+91' . $phoneNo;

        return $formattedPhoneNumber;
    }
}
