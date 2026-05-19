<?php

namespace App\Services;

use App\Models\IdempotencyKey;

class IdempotencyService
{
    public function find(string $key): ?IdempotencyKey
    {
        return IdempotencyKey::where('key', $key)->first();
    }

    public function store(
        string $key,
        string $requestHash,
        array $response
    ): IdempotencyKey {
        return IdempotencyKey::create([
            'key' => $key,
            'request_hash' => $requestHash,
            'response' => $response,
        ]);
    }

    public function isDuplicate(
        string $key,
        string $requestHash
    ): bool {
        $record = $this->find($key);

        if (! $record) {
            return false;
        }

        return $record->request_hash === $requestHash;
    }
}
