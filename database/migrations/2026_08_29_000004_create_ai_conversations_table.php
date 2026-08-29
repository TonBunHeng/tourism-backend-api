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
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('session_id', 100)->unique('uk_ai_conversations_session');
            $table->string('title', 150)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('language', 10)->default('en');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_ai_conversations_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('user_id', 'idx_ai_conversations_user_id');
            $table->index('last_message_at', 'idx_ai_conversations_last_msg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
