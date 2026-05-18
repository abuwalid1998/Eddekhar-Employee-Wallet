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
        Schema::create('payroll_events', function (Blueprint $table) {
            $table->id();

            $table->uuid('external_event_id')->unique();

            $table->string('type');

            $table->jsonb('payload');

            $table->string('status');

            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_events');
    }
};
