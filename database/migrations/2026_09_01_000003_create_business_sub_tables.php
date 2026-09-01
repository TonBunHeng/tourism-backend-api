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
        Schema::create('business_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->longText('image_url');
            $table->string('caption', 255)->nullable();
            $table->boolean('is_cover')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->index('business_id');
        });

        Schema::create('business_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->index('business_id');
        });

        Schema::create('business_hours', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->index(['business_id', 'day_of_week']);
        });

        Schema::create('business_promotions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->string('promo_code', 50)->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->longText('banner_url')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->index('business_id');
        });

        // Add business_id to reviews and make place_id nullable
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'business_id')) {
                    $table->unsignedInteger('business_id')->nullable()->after('place_id');
                    $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
                    $table->index('business_id');
                }
                $table->unsignedInteger('place_id')->nullable()->change();
            });
        }

        // Add business_id to events and make place_id nullable
        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                if (!Schema::hasColumn('events', 'business_id')) {
                    $table->unsignedInteger('business_id')->nullable()->after('place_id');
                    $table->foreign('business_id')->references('id')->on('businesses')->onDelete('set null');
                    $table->index('business_id');
                }
                $table->unsignedInteger('place_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('events') && Schema::hasColumn('events', 'business_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropForeign(['business_id']);
                $table->dropColumn('business_id');
            });
        }

        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'business_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropForeign(['business_id']);
                $table->dropColumn('business_id');
            });
        }

        Schema::dropIfExists('business_promotions');
        Schema::dropIfExists('business_hours');
        Schema::dropIfExists('business_services');
        Schema::dropIfExists('business_images');
    }
};
