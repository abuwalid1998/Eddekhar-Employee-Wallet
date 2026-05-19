<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Wallet;
use App\Services\IdempotencyService;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;

/**
 * @author Amjad Khaliliah
 */
class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService,
        private IdempotencyService $idempotencyService
    ) {
    }

    public function store(TransferRequest $request): JsonResponse
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

        $sourceWallet = Wallet::findOrFail($request->from_wallet_id);
        $destinationWallet = Wallet::findOrFail($request->to_wallet_id);

        $result = $this->transferService->transfer(
            sourceWallet: $sourceWallet,
            destinationWallet: $destinationWallet,
            amount: $request->amount,
            description: $request->description ?? ''
        );

        $response = [
            'message' => 'Transfer completed successfully.',
            'data' => [
                'debit' => (new TransactionResource($result['debit']))->resolve(),
                'credit' => (new TransactionResource($result['credit']))->resolve(),
            ],
        ];

        $this->idempotencyService->store($key, $requestHash, $response);

        return response()->json($response);
    }
}
