<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('availability_health_country_daily', function (Blueprint $table) {
            $table->id();

            $table->date('date')->index();
            $table->string('supplier')->default('hotelbeds')->index();
            $table->string('country', 10)->nullable()->index();

            $table->unsignedInteger('total_requests')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->unsignedInteger('timeout_count')->default(0);

            $table->unsignedInteger('avg_response_time_ms')->nullable();
            $table->unsignedTinyInteger('no_rooms_returned_pct')->nullable();

            $table->timestamps();

            $table->unique(['date', 'supplier', 'country'], 'availability_country_daily_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_health_country_daily');
    }
};
