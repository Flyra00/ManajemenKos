<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menandai invoice "pelunasan sisa" (mis. sisa 50% setelah uang muka).
     *
     * Invoice ini menagih PERIODE SEWA YANG SAMA dengan invoice awal, sehingga
     * pelunasannya tidak boleh memperpanjang masa aktif kontrak (dulu salah
     * menambah +30 hari karena kontrak sudah berstatus aktif).
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('is_settlement')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('is_settlement');
        });
    }
};
