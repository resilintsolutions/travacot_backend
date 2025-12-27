<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Device type: 'web', 'mobile', etc.
            $table->string('device_type')->nullable();

            $table->string('destination_country')->nullable();
            $table->string('destination_city')->nullable();

            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();

            $table->unsignedTinyInteger('adults')->default(1);
            $table->unsignedTinyInteger('children')->default(0);

            // Did this search eventually produce a booking?
            $table->boolean('success')->default(false);

            $table->unsignedInteger('response_ms')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
