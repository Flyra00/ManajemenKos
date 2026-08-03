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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lease_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->string('invoice_number')->unique();

            $table->decimal('amount',12,2)->default(0);

            $table->date('billing_period');

            $table->date('due_date');

            $table->datetime('payment_date')->nullable();

            $table->enum('payment_method', [
                'cash',
                'e_wallet',
                'bank_tf'
            ])->default('cash');

            $table->enum('status', [
                'paid',
                'pending',
                'unpaid',
                'overdue'
            ])->default('unpaid');

            $table->string('proof_img')->nullable();

            $table->foreignId('verified_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete()
            ;

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
