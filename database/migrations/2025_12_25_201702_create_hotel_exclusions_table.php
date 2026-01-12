<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hotel_exclusions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            // automatic | force_visible | force_hidden
            $table->enum('mode', ['automatic', 'force_visible', 'force_hidden'])
                  ->default('automatic');

            $table->string('reason')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();

            $table->timestamps();

            $table->unique('hotel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_exclusions');
    }
};
