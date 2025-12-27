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
        Schema::table('margin_rule_parameters', function (Blueprint $table) {
            $table->boolean('enable_demand_rule')->default(true);
            $table->boolean('enable_competitor_rule')->default(true);
            $table->boolean('enable_conversion_rule')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('margin_rule_parameters', function (Blueprint $table) {
            //
        });
    }
};
