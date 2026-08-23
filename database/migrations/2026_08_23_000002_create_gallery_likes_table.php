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
        Schema::create('gallery_likes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('gallery_media_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('gallery_media_id', 'fk_gallery_like_media')
                  ->references('id')
                  ->on('gallery_media')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('user_id', 'fk_gallery_like_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unique(['gallery_media_id', 'user_id'], 'uniq_gallery_media_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_likes');
    }
};
