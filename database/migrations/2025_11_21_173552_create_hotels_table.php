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
        Schema::create('hotels', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->index()->unique();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->string('vendor')->nullable(); // 'hotelbeds', 'local'
                $table->string('vendor_id')->nullable(); // supplier reference
                $table->decimal('lowest_rate', 10, 2)->nullable();
                $table->string('currency', 10)->nullable();
                $table->text('description')->nullable();
                $table->enum('status', ['draft','active','inactive','archived'])->default('draft');
                $table->json('meta')->nullable();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
