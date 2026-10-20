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
        Schema::create('tours', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('destination')->default('Siem Reap');
            $table->unsignedInteger('province_id')->nullable();
            $table->string('province')->nullable();
            $table->string('duration', 50)->default('1 Full Day');
            $table->unsignedInteger('duration_days')->default(1);
            $table->decimal('price', 10, 2)->default(50.00);
            $table->string('currency', 10)->default('USD');
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->string('badge')->nullable()->default('Featured');
            $table->string('category')->default('Cultural & Heritage');
            $table->longText('image_url')->nullable();
            $table->text('description')->nullable();
            $table->json('highlights')->nullable();
            $table->json('itinerary')->nullable();
            $table->json('included')->nullable();
            $table->unsignedInteger('max_travelers')->default(10);
            $table->string('group_size', 50)->default('Max 10 guests');
            $table->string('difficulty', 50)->default('Easy / Moderate Walking');
            $table->enum('status', ['active', 'draft', 'archived'])->default('active');
            $table->timestamps();

            $table->foreign('province_id', 'fk_tours_province')
                  ->references('id')
                  ->on('provinces')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->index('status', 'idx_tours_status');
            $table->index('destination', 'idx_tours_destination');
            $table->index('category', 'idx_tours_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
