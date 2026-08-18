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
        Schema::create('event_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->string('tag_name', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('event_id', 'fk_event_tags_event')
                  ->references('id')
                  ->on('events')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unique(['event_id', 'tag_name'], 'uk_event_tag');
            $table->index('event_id', 'idx_event_tags_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_tags');
    }
};
