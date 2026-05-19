<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawalRequest;
use App\Http\Resources\BankWithdrawalResource;
use App\Models\Wallet;
use App\Services\IdempotencyService;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;

/**
 * @author Amjad Khaliliah
 */
class WithdrawalController extends Controller
{
    public function __construct(
        private WithdrawalService $withdrawalService,
        private IdempotencyService $idempotencyService
    ) {}

    public function store(WithdrawalRequest $request): JsonResponse
    {
        $key = $request->header('Idempotency-Key');
        abort_unless($key, 422, 'Idempotency-Key header is required.');

        $payload = $request->validated();
        $requestHash = hash('sha256', json_encode($payload));
        $record = $this->idempotencyService->find($key);

        if ($record) {
            abort_unless($record->request_hash === $requestHash, 409, 'Idempotency key reused with different payload.');

            return response()->json($record->response, 200);
        }

        $wallet = Wallet::findOrFail($request->wallet_id);

        $withdrawal = $this->withdrawalService->initiateWithdrawal(
            wallet: $wallet,
            amount: $request->amount,
            description: $request->description ?? 'Employee withdrawal'
        );

        $response = [
            'data' => (new BankWithdrawalResource($withdrawal))->resolve(),
        ];

        $this->idempotencyService->store($key, $requestHash, $response);

        return response()->json($response, 201);
    }
}
