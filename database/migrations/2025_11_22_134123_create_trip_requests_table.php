<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trip_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('external_booking_id')->nullable();
            $table->string('status')->default('new'); // new, matched, failed, resolved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_requests');
    }
};
