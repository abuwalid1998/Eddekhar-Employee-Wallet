<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawalRequest;
use App\Http\Resources\BankWithdrawalResource;
use App\Models\Wallet;
use App\Services\WithdrawalService;

class WithdrawalController extends Controller
{
    public function __construct(
        private WithdrawalService $withdrawalService
    ) {}

    public function store(WithdrawalRequest $request): BankWithdrawalResource
    {
        $wallet = Wallet::findOrFail($request->wallet_id);

        $withdrawal = $this->withdrawalService->initiateWithdrawal(
            wallet: $wallet,
            amount: $request->amount,
            description: $request->description ?? 'Employee withdrawal'
        );

        return new BankWithdrawalResource($withdrawal);
    }
}
