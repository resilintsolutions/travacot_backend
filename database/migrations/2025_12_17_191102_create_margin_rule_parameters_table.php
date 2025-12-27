<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('margin_rule_parameters', function (Blueprint $table) {
            $table->id();

            // RULE A — Demand
            $table->integer('demand_high_threshold_rooms')->default(0);
            $table->decimal('demand_high_margin_increase_percent', 8, 2)->default(0);
            $table->decimal('demand_low_margin_decrease_percent', 8, 2)->default(0);

            // RULE B — Competitor
            $table->decimal('competitor_price_diff_threshold_percent', 8, 2)->default(0);
            $table->decimal('competitor_margin_decrease_percent', 8, 2)->default(0);

            // RULE C — Conversion
            $table->decimal('conversion_threshold_percent', 8, 2)->default(0);
            $table->decimal('conversion_margin_decrease_percent', 8, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('margin_rule_parameters');
    }
};
