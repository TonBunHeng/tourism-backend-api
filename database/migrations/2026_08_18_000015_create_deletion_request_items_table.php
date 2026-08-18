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
        Schema::create('deletion_request_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deletion_request_id');
            $table->string('item_type', 50);
            $table->unsignedInteger('item_id')->nullable();
            $table->string('item_name', 150);
            $table->string('category', 100)->nullable();
            $table->date('date_added')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('deletion_request_id', 'fk_deletion_items_request')
                  ->references('id')
                  ->on('deletion_requests')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('deletion_request_id', 'idx_deletion_items_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deletion_request_items');
    }
};
