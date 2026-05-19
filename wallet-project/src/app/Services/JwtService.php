<?php

namespace App\Services;

use App\Models\User;

/**
 * @author Amjad Khaliliah
 */
class JwtService
{
    public function generateToken(User $user, int $ttlMinutes = 120): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $now = time();

        $payload = [
            'iss' => config('app.name', 'employee-wallet'),
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => $now,
            'exp' => $now + ($ttlMinutes * 60),
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $this->secret(), true);

        return $encodedHeader.'.'.$encodedPayload.'.'.$this->base64UrlEncode($signature);
    }

    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $signature = $this->base64UrlDecode($encodedSignature);
        $expected = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $this->secret(), true);

        if (! hash_equals($expected, $signature)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (! is_array($payload) || ($payload['exp'] ?? 0) < time()) {
            return null;
        }

        return $payload;
    }

    private function secret(): string
    {
        return (string) config('app.key');
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
