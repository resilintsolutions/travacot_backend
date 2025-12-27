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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            // polymorphic relation so same media table can be used for hotels, rooms, brochures, etc.
            $table->morphs('mediable'); // mediable_type, mediable_id
            $table->string('collection')->default('images'); // images, brochures, gallery, video
            $table->string('file_name');
            $table->string('path'); // storage path (relative), e.g. hotels/12/image1.jpg
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size')->nullable(); // bytes
            $table->string('external_url')->nullable(); // original URL from supplier
            $table->json('meta')->nullable(); // any extra data from supplier
            $table->integer('position')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
