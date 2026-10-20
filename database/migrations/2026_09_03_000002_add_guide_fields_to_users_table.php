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
        Schema::table('users', function (Blueprint $table) {
            $table->string('license_no', 50)->nullable()->after('location');
            $table->json('languages')->nullable()->after('license_no');
            $table->decimal('daily_rate', 8, 2)->nullable()->after('languages');
            $table->unsignedInteger('experience_years')->nullable()->after('daily_rate');
            $table->text('specialties')->nullable()->after('experience_years');
            $table->decimal('rating', 3, 2)->default(5.00)->after('specialties');
            $table->unsignedInteger('reviews_count')->default(0)->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'license_no',
                'languages',
                'daily_rate',
                'experience_years',
                'specialties',
                'rating',
                'reviews_count'
            ]);
        });
    }
};
