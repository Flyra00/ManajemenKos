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
        Schema::create('maintenance_requests', function (Blueprint $table) {
    $table->id();

    $table->foreignId('room_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('tenant_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('title');

    $table->text('description');

    $table->string('image_path')->nullable();

    $table->enum('priority', [
        'low',
        'medium',
        'high',
    ])->default('medium');

    $table->enum('status', [
        'reported',
        'in_progress',
        'completed',
        'cancelled',
    ])->default('reported');

    $table->decimal('cost', 12, 2)
        ->default(0);

    $table->foreignId('handled_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->timestamp('reported_at');

    $table->timestamp('resolved_at')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
