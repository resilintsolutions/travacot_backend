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
        Schema::create('supplier_responses', function (Blueprint $table) {
            $table->id();
            $table->string('supplier')->index();
            $table->string('endpoint')->nullable();
            $table->json('request_payload')->nullable();
            $table->longText('response_body')->nullable();
            $table->integer('status_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_responses');
    }
};
