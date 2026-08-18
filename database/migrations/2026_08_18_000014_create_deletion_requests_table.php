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
        Schema::create('deletion_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->enum('request_type', ['account', 'item'])->default('account');
            $table->text('reason');
            $table->text('additional_info')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'archived'])->default('pending');
            $table->enum('urgency', ['critical', 'high', 'medium', 'low'])->default('low');
            $table->text('admin_notes')->nullable();
            $table->unsignedInteger('processed_by_user_id')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_deletion_requests_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('processed_by_user_id', 'fk_deletion_requests_processed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->index('user_id', 'idx_deletion_requests_user_id');
            $table->index('status', 'idx_deletion_requests_status');
            $table->index('urgency', 'idx_deletion_requests_urgency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deletion_requests');
    }
};
