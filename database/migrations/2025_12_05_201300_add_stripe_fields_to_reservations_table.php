<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Stripe payment intent id (for matching webhooks)
            $table->string('stripe_payment_intent_id')
                  ->nullable()
                  ->after('supplier_reference')
                  ->index();

            // High-level payment status: pending / succeeded / failed
            $table->string('payment_status')
                  ->default('pending')
                  ->after('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['stripe_payment_intent_id', 'payment_status']);
        });
    }
};
