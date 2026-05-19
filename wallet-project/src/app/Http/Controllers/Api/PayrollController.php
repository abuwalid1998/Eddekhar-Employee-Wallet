<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollEventRequest;
use App\Services\PayrollService;
use Illuminate\Http\JsonResponse;

class PayrollController extends Controller
{
    public function __construct(
        private PayrollService $payrollService
    ) {}

    public function store(PayrollEventRequest $request): JsonResponse
    {
        $event = $this->payrollService->processSalaryEvent(
            $request->validated()
        );

        return response()->json([
            'message' => 'Payroll event processed successfully.',
            'data' => [
                'event_id' => $event->id,
                'status' => $event->status,
            ],
        ]);
    }
}
