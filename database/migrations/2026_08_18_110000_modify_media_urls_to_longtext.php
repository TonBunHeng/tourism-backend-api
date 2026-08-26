<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `places` MODIFY `image_url` LONGTEXT NULL');
            DB::statement('ALTER TABLE `events` MODIFY `image_url` LONGTEXT NULL');
            DB::statement('ALTER TABLE `gallery_media` MODIFY `url` LONGTEXT NOT NULL');
            DB::statement('ALTER TABLE `review_images` MODIFY `image_url` LONGTEXT NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `places` MODIFY `image_url` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `events` MODIFY `image_url` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `gallery_media` MODIFY `url` VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE `review_images` MODIFY `image_url` VARCHAR(255) NOT NULL');
        }
    }
};
