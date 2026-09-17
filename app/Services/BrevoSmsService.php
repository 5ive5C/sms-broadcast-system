<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoSmsService
{
    protected const ENDPOINT = 'https://api.brevo.com/v3/transactionalSMS/send';

    /**
     * Send one SMS through Brevo's transactional SMS API.
     *
     * @return array{accepted: bool, message_id: ?string, error: ?string}
     */
    public function send(string $recipient, string $content, string $type = 'transactional', ?string $tag = null): array
    {
        $response = $this->request($recipient, $content, $type, $tag);

        if ($response->successful()) {
            return [
                'accepted' => true,
                'message_id' => $response->json('messageId') ?? $response->json('reference'),
                'error' => null,
            ];
        }

        Log::warning('Brevo SMS send failed', [
            'recipient' => $this->maskRecipient($recipient),
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return [
            'accepted' => false,
            'message_id' => null,
            'error' => $response->json('message') ?? 'iSMS gateway error (HTTP '.$response->status().')',
        ];
    }

    protected function request(string $recipient, string $content, string $type, ?string $tag): Response
    {
        return Http::withHeaders([
            'accept' => 'application/json',
            'api-key' => config('services.brevo.api_key'),
            'content-type' => 'application/json',
        ])
            ->timeout(15)
            ->post(self::ENDPOINT, array_filter([
                'sender' => config('services.brevo.sender_name'),
                'recipient' => $this->normalizeRecipient($recipient),
                'content' => $content,
                'type' => $type,
                'tag' => $tag,
                'unicodeEnabled' => ! $this->isGsm7($content),
            ], fn ($value) => $value !== null));
    }

    /**
     * Brevo expects the recipient with country code and no leading '+'.
     */
    protected function normalizeRecipient(string $recipient): string
    {
        return ltrim($recipient, '+');
    }

    protected function isGsm7(string $content): bool
    {
        $gsm7 = '@£$¥èéùìòÇ'.\chr(10).'Øø'.\chr(13).'ÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';

        foreach (mb_str_split($content) as $char) {
            if (! str_contains($gsm7, $char)) {
                return false;
            }
        }

        return true;
    }

    protected function maskRecipient(string $recipient): string
    {
        return strlen($recipient) <= 6 ? $recipient : substr($recipient, 0, -6).'••••'.substr($recipient, -2);
    }
}
