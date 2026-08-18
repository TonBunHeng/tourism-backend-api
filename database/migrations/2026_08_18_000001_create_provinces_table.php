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
        Schema::create('provinces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique('uk_provinces_name');
            $table->enum('type', ['Capital City', 'Province', 'Municipality'])->default('Province');
            $table->string('population', 50)->nullable();
            $table->string('area', 50)->nullable();
            $table->unsignedInteger('districts_count')->default(0);
            $table->unsignedInteger('communes_count')->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->string('icon', 50)->nullable();
            $table->text('description')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->timestamps();

            $table->index('status', 'idx_provinces_status');
            $table->index('type', 'idx_provinces_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
