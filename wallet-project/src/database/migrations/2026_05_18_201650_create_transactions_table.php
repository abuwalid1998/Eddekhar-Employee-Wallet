<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();

            $table->uuid('reference_id')->unique();

            $table->string('type');
            $table->string('direction');

            $table->bigInteger('amount');

            $table->string('currency', 3);

            $table->string('status');

            $table->text('description')->nullable();

            $table->jsonb('metadata')->nullable();

            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
