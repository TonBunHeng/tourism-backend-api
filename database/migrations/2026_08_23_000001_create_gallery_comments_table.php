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
        Schema::create('gallery_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('gallery_media_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->text('comment');
            $table->timestamps();

            $table->foreign('gallery_media_id', 'fk_gallery_comment_media')
                  ->references('id')
                  ->on('gallery_media')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('user_id', 'fk_gallery_comment_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('parent_id', 'fk_gallery_comment_parent')
                  ->references('id')
                  ->on('gallery_comments')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('gallery_media_id', 'idx_gallery_comment_media_id');
            $table->index('parent_id', 'idx_gallery_comment_parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_comments');
    }
};
