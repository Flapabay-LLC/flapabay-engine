<?php

namespace App\Traits;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Infobip\Api\SendSmsApi;
use Infobip\ApiException;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;


trait MySMS
{
    protected $client;

    public function __construct()
    {
        // Initialize Guzzle HTTP client with Infobip API base URL
        $this->client = new Client([
            'base_uri' => env('INFOBIP_BASE_URL'),
            'headers' => [
                'Authorization' => 'App ' . env('INFOBIP_API_KEY'),
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function sendOTP($user, $otp)
    {
        // Initialize the SendSmsApi client with Guzzle HTTP client
        // $sendSmsApi = new SendSmsApi($this->client);


        // Initialize Infobip client configuration
        $configuration = new Configuration();
        $configuration->setHost(env('INFOBIP_BASE_URL'));
        $configuration->setApiKey('Authorization', 'App ' . env('INFOBIP_API_KEY'));

        // Initialize the SendSmsApi client
        $sendSmsApi = new SendSmsApi(config: $configuration);

        // Create the message payload
        $message = [
            'messages' => [
                [
                    'channel' => 'SMS',
                    'sender' => env('INFOBIP_SENDER_ID'), // Make sure to set this in your .env
                    'destinations' => [
                        [
                            'to' => $user->phone
                        ]
                    ],
                    'from' => 'Flapabay',
                    'text' => 'Your OTP code is: ' . $otp . '. It expires in 5 minutes.'
                ]
            ]
        ];

        // Send the SMS message
        return $sendSmsApi->sendSmsMessage(new SmsAdvancedTextualRequest($message));

    }
}
