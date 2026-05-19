<?php

namespace Tests\Feature;

use App\Services\IdempotencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdempotencyServiceTest extends TestCase
{
    use RefreshDatabase;

    private IdempotencyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(IdempotencyService::class);
    }

    public function test_it_stores_keys(): void
    {
        $record = $this->service->store(
            'abc123',
            'hash123',
            ['status' => 'ok']
        );

        $this->assertEquals('abc123', $record->key);
    }

    public function test_it_detects_duplicates(): void
    {
        $this->service->store(
            'abc123',
            'hash123',
            ['status' => 'ok']
        );

        $result = $this->service->isDuplicate(
            'abc123',
            'hash123'
        );

        $this->assertTrue($result);
    }
}
