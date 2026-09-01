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
        Schema::create('businesses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('owner_id');
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->longText('description')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('province_id')->nullable();
            $table->string('address', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('price_range', 30)->nullable()->default('$$');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedInteger('verified_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->unsignedInteger('review_count')->default(0);
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('province_id')->references('id')->on('provinces')->nullOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();

            $table->index('owner_id');
            $table->index('category_id');
            $table->index('province_id');
            $table->index('status');
            $table->index('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
