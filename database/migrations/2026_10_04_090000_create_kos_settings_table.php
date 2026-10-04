<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengaturan operasional kos dipindahkan dari storage/app/kos_settings.json
     * ke database. File tidak bertahan di filesystem ephemeral platform deploy,
     * sehingga pengaturan (termasuk billing_due) hilang setiap kali deploy.
     */
    public function up(): void
    {
        Schema::create('kos_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('KosFly Residence');
            $table->string('address', 500)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedTinyInteger('map_zoom')->default(16);
            $table->unsignedTinyInteger('billing_due')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kos_settings');
    }
};
