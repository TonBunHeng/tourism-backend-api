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
        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('place_id');
            $table->unsignedTinyInteger('rating');
            $table->string('title', 150)->nullable();
            $table->text('comment');
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('dislikes_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['Approved', 'Pending', 'Rejected', 'Flagged'])->default('Pending');
            $table->timestamps();

            $table->foreign('user_id', 'fk_reviews_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('place_id', 'fk_reviews_place')
                  ->references('id')
                  ->on('places')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('user_id', 'idx_reviews_user_id');
            $table->index('place_id', 'idx_reviews_place_id');
            $table->index('status', 'idx_reviews_status');
            $table->index('rating', 'idx_reviews_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
