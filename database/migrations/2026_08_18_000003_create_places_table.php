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
        Schema::create('places', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('province_id')->nullable();
            $table->string('address', 255);
            $table->string('coordinates', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('description')->nullable();
            $table->string('best_time', 100)->nullable();
            $table->string('duration', 50)->nullable();
            $table->string('price', 50)->nullable()->default('Free');
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('visitors_count')->default(0);
            $table->string('image_url', 255)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['Active', 'Inactive', 'Pending'])->default('Active');
            $table->timestamps();

            $table->foreign('category_id', 'fk_places_category')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('province_id', 'fk_places_province')
                  ->references('id')
                  ->on('provinces')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->index('category_id', 'idx_places_category_id');
            $table->index('province_id', 'idx_places_province_id');
            $table->index('status', 'idx_places_status');
            $table->index('is_featured', 'idx_places_is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
