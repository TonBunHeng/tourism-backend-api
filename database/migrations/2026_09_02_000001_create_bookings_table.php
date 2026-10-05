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
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('booking_reference', 32)->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('service_id')->nullable();
            $table->string('customer_name', 150);
            $table->string('customer_email', 150);
            $table->string('customer_phone', 50)->nullable();
            $table->date('booking_date');
            $table->string('booking_time', 20)->nullable();
            $table->unsignedInteger('number_of_guests')->default(1);
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'rejected'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->text('special_requests')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('business_services')->nullOnDelete();

            $table->index('booking_reference');
            $table->index('user_id');
            $table->index('business_id');
            $table->index('service_id');
            $table->index('status');
            $table->index('booking_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
