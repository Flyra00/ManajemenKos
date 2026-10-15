<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class EmailSecurityService
{
    /**
     * Durasi jeda (cooldown) dalam satuan detik:
     * - Percobaan pertama dalam 1 sesi harian: 5 menit (300 detik).
     * - Percobaan kedua dan seterusnya: 10 menit (600 detik).
     */
    public const COOLDOWN_FIRST_ATTEMPT = 300; // 5 menit
    public const COOLDOWN_SUBSEQUENT    = 600; // 10 menit

    /**
     * Periksa apakah pengiriman email untuk identifier tertentu sedang dalam masa jeda (cooldown).
     *
     * @param string $action Nama aksi pengiriman email (contoh: 'password_reset', 'email_verification')
     * @param string $identifier Alamat email atau ID pengguna
     * @return int|null Mengembalikan sisa detik jika masih dalam masa cooldown, atau null jika boleh mengirim
     */
    public function getCooldownRemaining(string $action, string $identifier): ?int
    {
        $normalized = $this->normalizeIdentifier($identifier);
        $key = $this->getCooldownKey($action, $normalized);

        $expiresAt = Cache::get($key);
        if (! $expiresAt) {
            return null;
        }

        $remaining = (int) ($expiresAt - now()->timestamp);

        if ($remaining <= 0) {
            Cache::forget($key);
            return null;
        }

        return $remaining;
    }

    /**
     * Catat percobaan pengiriman email yang berhasil dan terapkan jeda bertingkat:
     * - Pengiriman ke-1 hari ini: jeda 5 menit (300 detik).
     * - Pengiriman ke-2 dan seterusnya hari ini: jeda 10 menit (600 detik).
     * Counter harian otomatis di-reset saat pergantian hari (end of day).
     *
     * @param string $action
     * @param string $identifier
     * @return int Durasi cooldown yang baru diterapkan (dalam detik)
     */
    public function recordSentAttempt(string $action, string $identifier): int
    {
        $normalized = $this->normalizeIdentifier($identifier);
        $today = now()->toDateString();
        $endOfDay = now()->endOfDay();

        // 1. Ambil dan tambahkan hitungan pengiriman harian
        $countKey = $this->getDailyCountKey($action, $normalized, $today);
        $currentCount = (int) Cache::get($countKey, 0);
        $newCount = $currentCount + 1;
        Cache::put($countKey, $newCount, $endOfDay);

        // 2. Tentukan durasi cooldown:
        // Pengiriman pertama di hari itu = 5 menit (300 detik)
        // Pengiriman kedua & seterusnya = 10 menit (600 detik)
        $cooldownSeconds = ($newCount === 1)
            ? self::COOLDOWN_FIRST_ATTEMPT
            : self::COOLDOWN_SUBSEQUENT;

        $expiresAt = now()->timestamp + $cooldownSeconds;

        // 3. Simpan waktu kedaluwarsa cooldown di cache (TTL 24 jam)
        $cooldownKey = $this->getCooldownKey($action, $normalized);
        Cache::put($cooldownKey, $expiresAt, 86400);

        return $cooldownSeconds;
    }

    /**
     * Ambil jumlah pengiriman email yang telah dilakukan hari ini.
     *
     * @param string $action
     * @param string $identifier
     * @return int
     */
    public function getDailyAttemptCount(string $action, string $identifier): int
    {
        $normalized = $this->normalizeIdentifier($identifier);
        $today = now()->toDateString();
        $countKey = $this->getDailyCountKey($action, $normalized, $today);

        return (int) Cache::get($countKey, 0);
    }

    /**
     * Hapus cooldown dan hitungan harian (untuk pengujian atau reset khusus).
     *
     * @param string $action
     * @param string $identifier
     * @return void
     */
    public function clear(string $action, string $identifier): void
    {
        $normalized = $this->normalizeIdentifier($identifier);
        $today = now()->toDateString();

        Cache::forget($this->getCooldownKey($action, $normalized));
        Cache::forget($this->getDailyCountKey($action, $normalized, $today));
    }

    /**
     * Format sisa detik menjadi teks bahasa Indonesia yang ramah.
     * Contoh: "5 menit", "4 menit 30 detik", "45 detik".
     *
     * @param int $seconds
     * @return string
     */
    public function formatRemainingTime(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} detik";
        }

        $minutes = (int) floor($seconds / 60);
        $remainder = $seconds % 60;

        if ($remainder === 0) {
            return "{$minutes} menit";
        }

        return "{$minutes} menit {$remainder} detik";
    }

    /**
     * Buat pesan error ramah pengguna yang menginformasikan sisa waktu cooldown.
     *
     * @param int $secondsRemaining
     * @param string $context
     * @return string
     */
    public function getCooldownErrorMessage(int $secondsRemaining, string $context = 'email'): string
    {
        $timeStr = $this->formatRemainingTime($secondsRemaining);

        return "Untuk mencegah spam, pengiriman {$context} dibatasi. Mohon tunggu {$timeStr} sebelum mencoba kembali.";
    }

    /**
     * Normalisasi string identifier (lowercase dan trim).
     */
    protected function normalizeIdentifier(string $identifier): string
    {
        return strtolower(trim($identifier));
    }

    /**
     * Kunci cache untuk masa cooldown aktif.
     */
    protected function getCooldownKey(string $action, string $identifier): string
    {
        return "email_security:cooldown:{$action}:{$identifier}";
    }

    /**
     * Kunci cache untuk counter pengiriman per hari.
     */
    protected function getDailyCountKey(string $action, string $identifier, string $date): string
    {
        return "email_security:count:{$action}:{$identifier}:{$date}";
    }
}
