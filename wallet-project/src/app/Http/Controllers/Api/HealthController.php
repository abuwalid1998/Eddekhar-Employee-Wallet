<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => false,
            'redis' => false,
        ];

        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $checks['database'] = true;
        } catch (\Throwable $e) {
        }

        try {
            \Illuminate\Support\Facades\Redis::connection()->ping();
            $checks['redis'] = true;
        } catch (\Throwable $e) {
        }

        $healthy = $checks['database'] && $checks['redis'];

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
        ], $healthy ? 200 : 500);
    }
}
