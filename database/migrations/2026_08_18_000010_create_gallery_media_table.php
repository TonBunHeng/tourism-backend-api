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
        Schema::create('gallery_media', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('url', 255);
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('place_id')->nullable();
            $table->string('file_size', 30)->nullable();
            $table->string('dimensions', 30)->nullable();
            $table->unsignedInteger('uploaded_by_user_id')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->enum('status', ['Published', 'Draft'])->default('Published');
            $table->timestamps();

            $table->foreign('category_id', 'fk_gallery_category')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreign('place_id', 'fk_gallery_place')
                  ->references('id')
                  ->on('places')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreign('uploaded_by_user_id', 'fk_gallery_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->index('type', 'idx_gallery_type');
            $table->index('status', 'idx_gallery_status');
            $table->index('place_id', 'idx_gallery_place_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_media');
    }
};
