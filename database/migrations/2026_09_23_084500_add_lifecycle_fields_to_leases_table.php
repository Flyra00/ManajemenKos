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
            $table->date('checkout_date')->nullable()->after('end_date');
            $table->decimal('deposit_deduction', 12, 2)->default(0)->after('deposit_amount');
            $table->decimal('deposit_refunded', 12, 2)->default(0)->after('deposit_deduction');
            $table->string('room_condition')->nullable()->after('status');
            $table->text('checkout_notes')->nullable()->after('note');
            $table->unsignedInteger('renewal_count')->default(0)->after('checkout_notes');
            $table->dateTime('last_renewed_at')->nullable()->after('renewal_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn([
                'checkout_date',
                'deposit_deduction',
                'deposit_refunded',
                'room_condition',
                'checkout_notes',
                'renewal_count',
                'last_renewed_at',
            ]);
        });
    }
};
