<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promo_engine_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('engine_status')->default(true);
            $table->json('enabled_modes')->nullable(); // ["light","normal","aggressive"]
            $table->boolean('auto_downgrade_enabled')->default(true);
            $table->decimal('min_margin_eligibility', 6, 2)->default(6.0);
            $table->decimal('safety_buffer', 6, 2)->default(2.0);
            $table->decimal('min_profit_after_promo', 6, 2)->default(4.0);
            $table->string('discount_strategy')->default('max_safe');
            $table->unsignedInteger('attribution_window_minutes')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_engine_settings');
    }
};
