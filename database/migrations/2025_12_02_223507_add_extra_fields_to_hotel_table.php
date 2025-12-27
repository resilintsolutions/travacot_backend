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
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('country_iso')->nullable()->after('country');
            $table->string('destination_code')->nullable()->after('country_iso');
            $table->string('destination_name')->nullable()->after('destination_code');
            $table->string('longitude')->nullable()->after('destination_name');
            $table->string('latitude')->nullable()->after('longitude');
            $table->decimal('highest_rate', 10, 2)->nullable()->after('lowest_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn([
                'country_iso',
                'destination_code',
                'destination_name',
                'longitude',
                'latitude',
                'highest_rate',
            ]);
        });
    }
};
