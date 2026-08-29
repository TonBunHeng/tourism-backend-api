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
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->text('endpoint');
            $table->text('public_key')->nullable(); // p256dh
            $table->text('auth_token')->nullable(); // auth secret
            $table->string('content_encoding', 30)->default('aesgcm');
            $table->string('user_agent', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_push_subscriptions_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('user_id', 'idx_push_subscriptions_user_id');
        });

        Schema::create('user_notification_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique();
            $table->boolean('push_enabled')->default(true);
            $table->boolean('events_enabled')->default(true);
            $table->boolean('messages_enabled')->default(true);
            $table->boolean('system_enabled')->default(true);
            $table->boolean('promotions_enabled')->default(true);
            $table->timestamps();

            $table->foreign('user_id', 'fk_user_notification_settings_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_settings');
        Schema::dropIfExists('push_subscriptions');
    }
};
