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
        Schema::table('leases', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'active',
                'completed',
                'cancelled'
            ])->default('active')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', [
                'cash',
                'e_wallet',
                'bank_tf',
                'qris'
            ])->default('cash')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->enum('status', [
                'active',
                'completed',
                'cancelled'
            ])->default('active')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', [
                'cash',
                'e_wallet',
                'bank_tf'
            ])->default('cash')->change();
        });
    }
};
