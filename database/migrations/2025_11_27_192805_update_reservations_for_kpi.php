<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'markup_amount')) {
                $table->decimal('markup_amount', 10, 2)
                    ->default(0)
                    ->after('total_price');
            }

            if (!Schema::hasColumn('reservations', 'channel')) {
                $table->string('channel')
                    ->nullable()
                    ->after('currency'); // web, mobile, agent
            }

            if (!Schema::hasColumn('reservations', 'check_in')) {
                $table->date('check_in')
                    ->nullable()
                    ->after('status');
            }

            if (!Schema::hasColumn('reservations', 'check_out')) {
                $table->date('check_out')
                    ->nullable()
                    ->after('check_in');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'markup_amount',
                'channel',
                'check_in',
                'check_out',
            ]);
        });
    }
};
