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
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique('uk_categories_name');
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#8B5CF6');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->index('status', 'idx_categories_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
