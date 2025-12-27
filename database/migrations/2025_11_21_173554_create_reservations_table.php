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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('confirmation_number')->nullable()->index();
            $table->foreignId('hotel_id')->constrained();
            $table->foreignId('room_id')->nullable()->constrained('rooms');
            $table->json('guest_info'); // names, emails, passports
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 10)->nullable();
            $table->enum('status', ['pending','confirmed','cancelled','failed'])->default('pending');
            $table->json('raw_response')->nullable(); // store supplier payload
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
