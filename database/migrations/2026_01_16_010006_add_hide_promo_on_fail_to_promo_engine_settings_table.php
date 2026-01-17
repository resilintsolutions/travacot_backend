<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('promo_engine_settings', function (Blueprint $table) {
            $table->boolean('hide_promo_on_fail')->default(false)->after('auto_downgrade_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('promo_engine_settings', function (Blueprint $table) {
            $table->dropColumn('hide_promo_on_fail');
        });
    }
};
