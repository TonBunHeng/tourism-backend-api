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
        Schema::create('favorites', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('place_id');
            $table->boolean('visited')->default(false);
            $table->date('saved_date');
            $table->timestamps();

            $table->foreign('user_id', 'fk_favorites_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('place_id', 'fk_favorites_place')
                  ->references('id')
                  ->on('places')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unique(['user_id', 'place_id'], 'uk_favorites_user_place');
            $table->index('user_id', 'idx_favorites_user_id');
            $table->index('place_id', 'idx_favorites_place_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
