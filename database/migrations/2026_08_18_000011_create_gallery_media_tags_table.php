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
        Schema::create('gallery_media_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('media_id');
            $table->string('tag_name', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('media_id', 'fk_gallery_tags_media')
                  ->references('id')
                  ->on('gallery_media')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unique(['media_id', 'tag_name'], 'uk_media_tag');
            $table->index('media_id', 'idx_gallery_tags_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_media_tags');
    }
};
