<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // required
            $table->string('email')->nullable()->unique();
            $table->string('phone', 50)->nullable();
            $table->string('company')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Helpful indexes for search (optional)
            $table->index(['name']);
            $table->index(['company']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
