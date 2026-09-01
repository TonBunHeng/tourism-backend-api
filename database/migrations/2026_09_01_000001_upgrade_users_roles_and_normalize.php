<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('user')->change();
        });

        // Normalize existing role data to canonical snake_case
        DB::table('users')->where('role', 'Super Admin')->update(['role' => 'super_admin']);
        DB::table('users')->where('role', 'Admin')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'Guide / Editor')->update(['role' => 'guide_editor']);
        DB::table('users')->where('role', 'User')->update(['role' => 'user']);
        DB::table('users')->where('role', 'Business Owner')->update(['role' => 'business_owner']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'Super Admin']);
        DB::table('users')->where('role', 'admin')->update(['role' => 'Admin']);
        DB::table('users')->where('role', 'guide_editor')->update(['role' => 'Guide / Editor']);
        DB::table('users')->where('role', 'user')->update(['role' => 'User']);
        DB::table('users')->where('role', 'business_owner')->update(['role' => 'User']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['Super Admin', 'Admin', 'Guide / Editor', 'User'])->default('User')->change();
        });
    }
};
