<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('margin_rules_settings', function (Blueprint $table) {
            $table->id();

            $table->enum('scope', ['global', 'country', 'city']);
            $table->string('country', 10)->nullable();
            $table->string('city', 50)->nullable();

            $table->decimal('default_margin_percent', 8, 2)->default(0);
            $table->decimal('min_margin_percent', 8, 2)->nullable();
            $table->decimal('max_margin_percent', 8, 2)->nullable();

            $table->boolean('is_enabled')->default(1);

            $table->timestamps();

            $table->index(['scope', 'country']);
            $table->index(['scope', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('margin_rules_settings');
    }
};
