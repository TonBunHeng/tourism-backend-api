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
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('type', 50)->default('system'); // deletion_request, review, chat, user, event, system
            $table->string('category', 50)->default('System'); // Alerts, Reviews, Messages, Users, System
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('link', 255)->nullable();
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_notifications_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->index(['user_id', 'read'], 'idx_notifications_user_read');
            $table->index('category', 'idx_notifications_category');
            $table->index('created_at', 'idx_notifications_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
