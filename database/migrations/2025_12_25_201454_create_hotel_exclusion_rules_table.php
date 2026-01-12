<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hotel_exclusion_rules', function (Blueprint $table) {
            $table->id();

            $table->float('min_rating')->default(7.0);
            $table->integer('min_reviews')->default(5);

            $table->boolean('exclude_no_images')->default(true);
            $table->boolean('exclude_no_description')->default(true);
            $table->boolean('exclude_inactive')->default(true);

            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_exclusion_rules');
    }
};
