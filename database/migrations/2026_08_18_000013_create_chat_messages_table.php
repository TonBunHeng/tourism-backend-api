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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chat_id');
            $table->enum('sender_type', ['user', 'admin', 'ai']);
            $table->unsignedInteger('sender_user_id')->nullable();
            $table->text('message_text');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_ai')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('chat_id', 'fk_chat_messages_chat')
                  ->references('id')
                  ->on('chats')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('sender_user_id', 'fk_chat_messages_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->index('chat_id', 'idx_chat_messages_chat_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
