<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateWalletRequest;
use App\Http\Requests\StoreWalletRequest;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WalletController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $wallets = Wallet::query()
            ->when(request('employee_id'), function ($query) {
                $query->where('employee_id', request('employee_id'));
            })
            ->when(request('currency'), function ($query) {
                $query->where('currency', request('currency'));
            })
            ->latest()
            ->paginate(15);

        return WalletResource::collection($wallets);
    }

    public function store(CreateWalletRequest $request): AnonymousResourceCollection
    {
        $request->validated();

        $wallet = Wallet::create([
            'employee_id' => $request->employee_id,
            'type' => $request->type,
            'currency' => strtoupper($request->currency),
            'available_balance' => 0,
            'reserved_balance' => 0,
            'status' => 'active',
        ]);

        return WalletResource::collection(Wallet::paginate());    }

    public function show(Wallet $wallet): WalletResource
    {
        return new WalletResource($wallet);
    }

    public function transactions(Wallet $wallet): AnonymousResourceCollection
    {
        $transactions = $wallet->transactions()
            ->when(request('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->when(request('type'), function ($query) {
                $query->where('type', request('type'));
            })
            ->latest()
            ->paginate(20);

        return TransactionResource::collection(
            $wallet->transactions()->latest()->paginate()
        );
    }
}
