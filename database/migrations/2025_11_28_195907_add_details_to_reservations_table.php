<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {

            // Pax
            $table->unsignedTinyInteger('adults')->default(2)->after('check_out');
            $table->unsignedTinyInteger('children')->default(0)->after('adults');

            // Basic customer details
            $table->string('customer_name')->nullable()->after('guest_info');
            $table->string('customer_email')->nullable()->after('customer_name');

            // Channel / reference info
            $table->string('booking_channel', 50)->nullable()->after('customer_email'); // Website, Mobile, Agent, etc.
            $table->string('supplier_reference')->nullable()->after('booking_channel'); // e.g. Hotelbeds booking ref
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'adults',
                'children',
                'customer_name',
                'customer_email',
                'booking_channel',
                'supplier_reference',
            ]);
        });
    }
};
