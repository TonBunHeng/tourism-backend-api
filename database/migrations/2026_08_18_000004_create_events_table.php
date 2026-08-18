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
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 200);
            $table->string('category', 100);
            $table->text('description')->nullable();
            $table->string('location', 255);
            $table->unsignedInteger('place_id')->nullable();
            $table->unsignedInteger('province_id')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('start_time', 20)->nullable();
            $table->unsignedInteger('attendees_count')->default(0);
            $table->string('price', 50)->nullable()->default('Free');
            $table->string('organizer', 150)->nullable();
            $table->boolean('featured')->default(false);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->string('image_url', 255)->nullable();
            $table->enum('status', ['Upcoming', 'Ongoing', 'Completed', 'Cancelled', 'Scheduled'])->default('Upcoming');
            $table->timestamps();

            $table->foreign('place_id', 'fk_events_place')
                  ->references('id')
                  ->on('places')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreign('province_id', 'fk_events_province')
                  ->references('id')
                  ->on('provinces')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->index('status', 'idx_events_status');
            $table->index('start_date', 'idx_events_start_date');
            $table->index('featured', 'idx_events_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
