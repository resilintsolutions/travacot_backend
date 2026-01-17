<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('promo_mode')->nullable()->after('payment_status');
            $table->decimal('promo_discount_percent', 6, 2)->nullable()->after('promo_mode');
            $table->decimal('promo_final_margin', 6, 2)->nullable()->after('promo_discount_percent');
            $table->boolean('promo_attributed')->default(false)->after('promo_final_margin');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'promo_mode',
                'promo_discount_percent',
                'promo_final_margin',
                'promo_attributed',
            ]);
        });
    }
};
