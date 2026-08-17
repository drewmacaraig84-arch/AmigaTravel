<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    /**
     * Send push notification to a specific user by their user ID topic.
     */
    public static function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        return self::sendToTopic("user_{$userId}", $title, $body, $data);
    }

    /**
     * Send push notification to all users subscribed to the global topic.
     */
    public static function sendToAll(string $title, string $body, array $data = []): bool
    {
        return self::sendToTopic('all_users', $title, $body, $data);
    }

    /**
     * Send push notification to an FCM topic (e.g. user_5 or all_users).
     */
    public static function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        return self::sendMessage([
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => self::formatDataPayload($data),
            'android' => [
                'priority' => 'HIGH',
                'notification' => [
                    'channel_id' => 'amiga_travel_alerts',
                    'sound' => 'default',
                ],
            ],
        ]);
    }

    /**
     * Send push notification to a specific FCM device registration token.
     */
    public static function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        return self::sendMessage([
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => self::formatDataPayload($data),
            'android' => [
                'priority' => 'HIGH',
                'notification' => [
                    'channel_id' => 'amiga_travel_alerts',
                    'sound' => 'default',
                ],
            ],
        ]);
    }

    /**
     * Dispatch raw message array to FCM HTTP v1 API.
     */
    private static function sendMessage(array $messagePayload): bool
    {
        try {
            $credentials = self::getCredentials();
            if (! $credentials || empty($credentials['project_id'])) {
                Log::warning('FirebasePushService: Missing Firebase credentials');
                return false;
            }

            $accessToken = self::getAccessToken($credentials);
            if (! $accessToken) {
                Log::error('FirebasePushService: Failed to obtain Google OAuth2 access token');
                return false;
            }

            $projectId = $credentials['project_id'];
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type'  => 'application/json',
            ])->post($url, [
                'message' => $messagePayload,
            ]);

            if ($response->successful()) {
                Log::info('FirebasePushService: FCM push sent successfully', [
                    'target' => $messagePayload['topic'] ?? ($messagePayload['token'] ?? 'unknown'),
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('FirebasePushService: FCM API returned error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('FirebasePushService exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * FCM data payload must be string -> string key-values.
     */
    private static function formatDataPayload(array $data): array
    {
        $formatted = [];
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $formatted[(string) $key] = json_encode($value);
            } else {
                $formatted[(string) $key] = (string) $value;
            }
        }
        return $formatted;
    }

    /**
     * Load credentials from ENV string or storage file.
     */
    private static function getCredentials(): ?array
    {
        $rawJson = env('FIREBASE_CREDENTIALS_JSON');

        if (! empty($rawJson)) {
            // Check if base64 encoded
            $decoded = base64_decode($rawJson, true);
            if ($decoded && str_contains($decoded, 'private_key')) {
                $parsed = json_decode($decoded, true);
                if (is_array($parsed)) return $parsed;
            }

            $parsed = json_decode($rawJson, true);
            if (is_array($parsed)) return $parsed;
        }

        $filePath = storage_path('app/firebase_credentials.json');
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $parsed = json_decode($content, true);
            if (is_array($parsed)) return $parsed;
        }

        return null;
    }

    private static ?string $cachedToken = null;
    private static int $cachedTokenExpiry = 0;

    /**
     * Get or generate a Google OAuth2 access token for FCM.
     */
    private static function getAccessToken(array $credentials): ?string
    {
        if (self::$cachedToken && time() < self::$cachedTokenExpiry) {
            return self::$cachedToken;
        }

        try {
            $now = time();
            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT',
            ];

            $claim = [
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'exp'   => $now + 3600,
                'iat'   => $now,
            ];

            $base64UrlHeader = self::base64UrlEncode(json_encode($header));
            $base64UrlClaim  = self::base64UrlEncode(json_encode($claim));

            $signatureInput = $base64UrlHeader . '.' . $base64UrlClaim;
            $signature = '';

            $privateKey = openssl_pkey_get_private($credentials['private_key']);
            if (! $privateKey) {
                Log::error('FirebasePushService: Invalid private key format');
                return null;
            }

            if (! openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                Log::error('FirebasePushService: Failed to sign JWT with openssl');
                return null;
            }

            $base64UrlSignature = self::base64UrlEncode($signature);
            $jwt = $signatureInput . '.' . $base64UrlSignature;

            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($tokenResponse->successful()) {
                $token = $tokenResponse->json('access_token');
                if ($token) {
                    self::$cachedToken = $token;
                    self::$cachedTokenExpiry = $now + 3000;
                    return $token;
                }
            }

            Log::error('FirebasePushService: Token request failed', [
                'status' => $tokenResponse->status(),
                'body'   => $tokenResponse->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('FirebasePushService getAccessToken exception: ' . $e->getMessage());
            return null;
        }
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
