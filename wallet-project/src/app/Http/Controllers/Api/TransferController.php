<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Wallet;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService
    ) {
    }

    public function store(TransferRequest $request): JsonResponse
    {
        $sourceWallet = Wallet::findOrFail(
            $request->from_wallet_id
        );

        $destinationWallet = Wallet::findOrFail(
            $request->to_wallet_id
        );

        $result = $this->transferService->transfer(
            sourceWallet: $sourceWallet,
            destinationWallet: $destinationWallet,
            amount: $request->amount,
            description: $request->description ?? ''
        );

        return response()->json([
            'message' => 'Transfer completed successfully.',
            'data' => [
                'debit' => (new TransactionResource(
                    $result['debit']
                ))->resolve(),

                'credit' => (new TransactionResource(
                    $result['credit']
                ))->resolve(),
            ],
        ]);
    }
}
