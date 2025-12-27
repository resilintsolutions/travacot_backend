<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('health_event_logs', function (Blueprint $table) {
            $table->date('event_date')->nullable()->after('id');
        });

        // Backfill existing rows safely
        DB::table('health_event_logs')
            ->whereNull('event_date')
            ->update(['event_date' => DB::raw('DATE(created_at)')]);

        Schema::table('health_event_logs', function (Blueprint $table) {
            $table->date('event_date')->nullable(false)->change();
            $table->index(['event_date', 'domain']);
        });
    }

    public function down(): void
    {
        Schema::table('health_event_logs', function (Blueprint $table) {
            $table->dropIndex(['event_date', 'domain']);
            $table->dropColumn('event_date');
        });
    }
};
