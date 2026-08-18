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
        Schema::create('review_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('review_id');
            $table->string('image_url', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('review_id', 'fk_review_images_review')
                  ->references('id')
                  ->on('reviews')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('review_id', 'idx_review_images_review_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_images');
    }
};
