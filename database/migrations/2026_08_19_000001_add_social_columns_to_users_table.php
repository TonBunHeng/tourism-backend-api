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
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider', 50)->nullable()->after('password_hash');
            $table->string('provider_id', 150)->nullable()->after('provider');
            $table->string('provider_email', 150)->nullable()->after('provider_id');
            $table->timestamp('email_verified_at')->nullable()->after('provider_email');

            $table->index(['provider', 'provider_id'], 'idx_users_provider_provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_provider_provider_id');
            $table->dropColumn(['provider', 'provider_id', 'provider_email', 'email_verified_at']);
        });
    }
};
