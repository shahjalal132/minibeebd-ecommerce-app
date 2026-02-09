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
        Schema::create('incomplete_orders', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('delivery_charge_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('from_number')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key
            $table->foreign('delivery_charge_id')->references('id')->on('delivery_charges')->onDelete('set null');

            // Indexes
            $table->index('session_id');
            $table->index('ip_address');
            $table->index('phone');
            $table->index(['session_id', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomplete_orders');
    }
};
