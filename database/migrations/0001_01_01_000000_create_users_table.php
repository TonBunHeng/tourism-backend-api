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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('email', 150)->unique('uk_users_email');
            $table->string('phone', 30)->nullable();
            $table->string('password_hash', 255)->nullable();
            $table->longText('avatar')->nullable();
            $table->enum('role', ['Super Admin', 'Admin', 'Guide / Editor', 'User'])->default('User');
            $table->enum('status', ['Active', 'Inactive', 'Suspended'])->default('Active');
            $table->string('location', 100)->nullable();
            $table->boolean('verified')->default(false);
            $table->boolean('two_factor_auth')->default(false);
            $table->enum('subscription', ['Free', 'Basic', 'Premium'])->default('Free');
            $table->enum('activity_level', ['Low', 'Medium', 'High'])->default('Low');
            $table->text('bio')->nullable();
            $table->dateTime('last_active_at')->nullable();
            $table->timestamps();

            $table->index('role', 'idx_users_role');
            $table->index('status', 'idx_users_status');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
