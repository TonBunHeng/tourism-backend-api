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
        Schema::create('review_replies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('review_id');
            $table->unsignedInteger('user_id');
            $table->text('comment');
            $table->timestamps();

            $table->foreign('review_id', 'fk_review_replies_review')
                  ->references('id')
                  ->on('reviews')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('user_id', 'fk_review_replies_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('review_id', 'idx_review_replies_review_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_replies');
    }
};
