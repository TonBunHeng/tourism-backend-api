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
        Schema::create('chats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('category', 100)->default('Travel Planning');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['active', 'closed', 'archived'])->default('active');
            $table->unsignedInteger('unread_count')->default(0);
            $table->text('last_message')->nullable();
            $table->string('last_message_time', 30)->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_chats_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('user_id', 'idx_chats_user_id');
            $table->index('status', 'idx_chats_status');
            $table->index('priority', 'idx_chats_priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
