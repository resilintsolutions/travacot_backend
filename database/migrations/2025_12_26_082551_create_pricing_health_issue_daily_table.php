<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_health_issue_dailies', function (Blueprint $table) {
            $table->id();

            $table->date('date')->index();

            $table->string('supplier', 50)
                ->default('hotelbeds')
                ->index();

            /**
             * Issue types:
             * - below_msp
             * - missing_tax
             * - zero_rate
             * - price_changed
             */
            $table->string('issue', 50)->index();

            // Issue counters
            $table->unsignedInteger('total')->default(0);

            // Optional breakdowns (kept for future extensibility)
            $table->unsignedInteger('price_changed_count')->default(0);
            $table->unsignedInteger('missing_price_count')->default(0);

            $table->timestamps();

            // Ensure one row per issue per day per supplier
            $table->unique(
                ['date', 'supplier', 'issue'],
                'pricing_issue_daily_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_health_issue_dailies');
    }
};
