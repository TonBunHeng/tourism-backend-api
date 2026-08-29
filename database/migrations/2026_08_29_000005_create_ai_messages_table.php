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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ai_conversation_id');
            $table->enum('role', ['user', 'assistant', 'system'])->default('user');
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('ai_conversation_id', 'fk_ai_messages_conversation')
                  ->references('id')
                  ->on('ai_conversations')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('ai_conversation_id', 'idx_ai_messages_conversation_id');
            $table->index('created_at', 'idx_ai_messages_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
