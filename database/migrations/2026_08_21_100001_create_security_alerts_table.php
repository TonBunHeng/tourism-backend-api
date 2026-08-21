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
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->default('failed_login_threshold')->index();
            $table->string('email', 150)->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->integer('attempts')->default(1);
            $table->text('message');
            $table->boolean('is_read')->default(false)->index();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};
