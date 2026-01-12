<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pricing_health_dailies', function (Blueprint $table) {
            $table->id();

            $table->date('date')->index();
            $table->string('supplier', 50)->index();

            $table->unsignedInteger('total_quotes')->default(0);
            $table->unsignedInteger('below_msp_count')->default(0);
            $table->unsignedInteger('missing_tax_count')->default(0);

            $table->timestamps();

            $table->unique(['date', 'supplier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_health_dailies');
    }
};
