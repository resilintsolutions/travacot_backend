<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('msp_settings', function (Blueprint $table) {
            $table->id();

            // Scope: global / country / city
            $table->enum('scope', ['global', 'country', 'city'])->default('global')->index();

            // country/city depending on scope
            $table->string('country')->nullable()->index();
            $table->string('city')->nullable()->index();

            // Minimum Selling Price (money)
            $table->decimal('msp_amount', 10, 2)->default(0.00);

            // Currency code (ISO-ish)
            $table->string('currency', 10)->default('USD');

            $table->timestamps();

            // Prevent duplicate rows for the same scope/country/city combination
            $table->unique(['scope', 'country', 'city'], 'msp_scope_country_city_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msp_settings');
    }
};
