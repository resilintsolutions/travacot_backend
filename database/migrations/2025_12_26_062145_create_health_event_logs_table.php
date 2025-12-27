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
        Schema::create('health_event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            // availability | pricing | recheck | booking | content

            $table->string('action');
            // search | quote | recheck | book | validate

            $table->string('status');
            // success | failure | timeout | partial

            $table->string('country')->nullable();
            $table->string('destination')->nullable();

            $table->integer('response_time_ms')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_event_logs');
    }
};
