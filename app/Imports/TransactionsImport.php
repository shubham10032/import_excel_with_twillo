<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Twilio\Rest\Client;
use App\Jobs\SendSMSJob;
use Illuminate\Support\Facades\Queue;

class TransactionsImport implements ToModel, WithHeadingRow, WithChunkReading
{
    private $users;

    public function __construct()
    {
        $this->users = User::all(['id', 'name'])->pluck('name', 'id');
    }

    public function model(array $row)
    {
        
        $transaction = new Transaction([
            'description' => $row['description'],
            'amount' => $row['amount'],
            'phone_no'=> $row['phone_no'],
            'user_id' => isset($this->users[$row['user_id']]) ? $row['user_id'] : null,
            'created_at' => $row['created_at']
        ]);

        // Send message via Twilio
        // SendSMSJob::dispatch($row['phone_no'], "Your transaction details: Description - {$row['description']}, Amount - {$row['amount']}");
        $this->sendMessage($row['phone_no'], "Your transaction details: Description - {$row['description']}, Amount - {$row['amount']}");
        return $transaction;
    }

    private function sendMessage($phoneNo, $message)
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

    public function chunkSize(): int
    {
        return 5000;
    }
}
