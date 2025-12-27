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
        Schema::create('availability_health_dailies', function (Blueprint $table) {
            $table->id();

            $table->date('date')->index();
            $table->string('supplier', 50)->index();   // hotelbeds
            $table->string('country', 5)->nullable()->index();

            $table->unsignedInteger('total_requests')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->unsignedInteger('timeout_count')->default(0);

            $table->unsignedInteger('avg_response_time_ms')->nullable();
            $table->unsignedInteger('p95_response_time_ms')->nullable();

            $table->unsignedInteger('hotels_returned_avg')->nullable();

            $table->timestamps();

            $table->unique(['date', 'supplier', 'country']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_health_dailies');
    }
};
