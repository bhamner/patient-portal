<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Twilio\Rest\Client;

class TwilioSmsService implements SmsSenderInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly string $from,
    ) {
    }

    public function send(string $to, string $message): void
    {
        $to = trim($to);

        if ($to === '' || ! str_starts_with($to, '+')) {
            throw new InvalidArgumentException('Recipient phone number must be E.164 format, e.g. +15551234567.');
        }

        try {
            $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send SMS via Twilio', [
                'to' => $to,
                'message' => $message,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

