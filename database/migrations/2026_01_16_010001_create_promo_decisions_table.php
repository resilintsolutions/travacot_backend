<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promo_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();
            $table->string('mode')->nullable();
            $table->decimal('discount_percent', 6, 2)->nullable();
            $table->decimal('original_margin', 6, 2)->nullable();
            $table->decimal('final_margin', 6, 2)->nullable();
            $table->string('status')->default('none'); // applied, none
            $table->string('reason')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_decisions');
    }
};
