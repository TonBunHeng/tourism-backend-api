<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->unique()->index();
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('blocked_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('blocked_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
    }
};
