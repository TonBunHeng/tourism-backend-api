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
        Schema::create('trip_itineraries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trip_id');
            $table->unsignedInteger('place_id')->nullable();
            $table->unsignedInteger('day_number')->default(1);
            $table->string('time_slot', 50)->nullable();
            $table->string('activity', 255);
            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->foreign('trip_id', 'fk_trip_itineraries_trip')
                  ->references('id')
                  ->on('trips')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('place_id', 'fk_trip_itineraries_place')
                  ->references('id')
                  ->on('places')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->index('trip_id', 'idx_trip_itineraries_trip_id');
            $table->index('place_id', 'idx_trip_itineraries_place_id');
            $table->index(['trip_id', 'day_number', 'sort_order'], 'idx_trip_itineraries_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_itineraries');
    }
};
