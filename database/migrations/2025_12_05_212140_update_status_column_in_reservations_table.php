<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Change status to VARCHAR(50) to avoid truncation errors
            $table->string('status', 50)->change();
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Rollback to previous size (adjust to your original length)
            $table->string('status', 20)->change();
        });
    }
};
