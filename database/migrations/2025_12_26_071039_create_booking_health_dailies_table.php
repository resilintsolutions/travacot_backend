<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_health_dailies', function (Blueprint $table) {
            $table->id();

            $table->date('date')->index();
            $table->string('supplier', 50)->index();

            $table->unsignedInteger('total_attempts')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->unsignedInteger('refund_count')->default(0);

            $table->unsignedInteger('avg_response_time_ms')->nullable();

            $table->timestamps();

            $table->unique(['date', 'supplier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_health_dailies');
    }
};

