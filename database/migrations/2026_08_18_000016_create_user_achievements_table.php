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
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('achievement_name', 100);
            $table->string('description', 255)->nullable();
            $table->string('icon', 50)->nullable();
            $table->boolean('unlocked')->default(false);
            $table->dateTime('unlocked_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_achievements_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unique(['user_id', 'achievement_name'], 'uk_user_achievement');
            $table->index('user_id', 'idx_user_achievements_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
