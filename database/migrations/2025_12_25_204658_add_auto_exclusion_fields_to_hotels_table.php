<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->boolean('auto_hidden')
                  ->default(false)
                  ->after('status')
                  ->index();

            $table->json('auto_exclusion_reasons')
                  ->nullable()
                  ->after('auto_hidden');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn([
                'auto_hidden',
                'auto_exclusion_reasons'
            ]);
        });
    }
};
