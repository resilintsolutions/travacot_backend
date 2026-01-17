<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marketplace_resales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['listed', 'sold', 'withdrawn'])->default('listed')->index();
            $table->decimal('listed_price', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->timestamp('listed_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->unique(['reservation_id'], 'marketplace_resale_reservation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_resales');
    }
};
