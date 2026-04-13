<?php

namespace App\Services;

class LiveKitTokenService
{
    private string $apiKey;
    private string $apiSecret;

    public function __construct()
    {
        $this->apiKey = config('services.livekit.api_key');
        $this->apiSecret = config('services.livekit.api_secret');
    }

    /**
     * Generate a LiveKit access token for a participant.
     */
    public function generateToken(
        string $roomName,
        string $participantName,
        bool $canPublish = true,
        bool $canSubscribe = true,
        bool $isAdmin = false,
        int $ttl = 86400
    ): string {
        $now = time();

        $claims = [
            'iss' => $this->apiKey,
            'sub' => $participantName,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
            'jti' => $participantName . '-' . bin2hex(random_bytes(8)),
            'video' => [
                'roomJoin' => true,
                'room' => $roomName,
                'canPublish' => $canPublish,
                'canSubscribe' => $canSubscribe,
                'canPublishData' => true,
                'roomAdmin' => $isAdmin,
                'roomCreate' => $isAdmin,
            ],
            'metadata' => json_encode([
                'name' => $participantName,
                'admin' => $isAdmin,
            ]),
        ];

        return $this->encodeJwt($claims);
    }

    /**
     * Encode JWT using HMAC-SHA256.
     */
    private function encodeJwt(array $payload): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ]));

        $payload = $this->base64UrlEncode(json_encode($payload));

        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $this->apiSecret, true)
        );

        return "$header.$payload.$signature";
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
