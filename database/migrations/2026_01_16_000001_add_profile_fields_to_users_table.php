<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('country')->nullable()->after('email');
            $table->string('city')->nullable()->after('country');
            $table->string('address')->nullable()->after('city');
            $table->string('phone_number')->nullable()->after('address');
            $table->string('country_code', 10)->nullable()->after('phone_number');
            $table->timestamp('identity_verified_at')->nullable()->after('country_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'country',
                'city',
                'address',
                'phone_number',
                'country_code',
                'identity_verified_at',
            ]);
        });
    }
};
