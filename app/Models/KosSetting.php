<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan operasional kos.
 *
 * Disimpan sebagai satu baris tunggal di tabel `kos_settings`, menggantikan
 * storage/app/kos_settings.json yang hilang setiap kali aplikasi di-deploy
 * ke filesystem ephemeral.
 */
class KosSetting extends Model
{
    /**
     * Nilai bawaan untuk kolom yang benar-benar tersimpan di database.
     */
    public const COLUMN_DEFAULTS = [
        'name'        => 'KosFly Residence',
        'address'     => 'Jl. Sukabirus No. 12, Dayeuhkolot, Bandung',
        'phone'       => '0812-3456-7890',
        'email'       => 'kontak@kosfly.com',
        'latitude'    => -6.9740,
        'longitude'   => 107.6305,
        'map_zoom'    => 16,
        'billing_due' => 10,
    ];

    /**
     * Data rekening yang dipakai dokumen cetak (invoice, kuitansi, kontrak).
     * Belum dapat diubah lewat halaman Pengaturan, jadi masih berupa bawaan.
     */
    public const BANK_DEFAULTS = [
        'bank_name'    => 'BCA',
        'bank_account' => '123-456-7890',
        'bank_holder'  => 'Pengelola KosFly',
    ];

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'latitude',
        'longitude',
        'map_zoom',
        'billing_due',
    ];

    protected $casts = [
        'latitude'    => 'float',
        'longitude'   => 'float',
        'map_zoom'    => 'integer',
        'billing_due' => 'integer',
    ];

    /**
     * Baris pengaturan tunggal. Dibuat dengan nilai bawaan bila belum ada.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], static::COLUMN_DEFAULTS);
    }

    /**
     * Pengaturan dalam bentuk array, siap dikonsumsi view dan service.
     *
     * Nilai yang masih null di database dikembalikan ke bawaan, sehingga
     * view tidak perlu menulis fallback sendiri-sendiri.
     */
    public function toSettingsArray(): array
    {
        $defaults = static::COLUMN_DEFAULTS;

        return array_merge(static::BANK_DEFAULTS, [
            'name'        => $this->name        ?? $defaults['name'],
            'address'     => $this->address     ?? $defaults['address'],
            'phone'       => $this->phone       ?? $defaults['phone'],
            'email'       => $this->email       ?? $defaults['email'],
            'latitude'    => $this->latitude    ?? $defaults['latitude'],
            'longitude'   => $this->longitude   ?? $defaults['longitude'],
            'map_zoom'    => $this->map_zoom    ?? $defaults['map_zoom'],
            'billing_due' => $this->billing_due ?? $defaults['billing_due'],
        ]);
    }

    /**
     * Pintasan: pengaturan kos sebagai array tanpa menyentuh database
     * bila barisnya memang belum pernah dibuat.
     */
    public static function settings(): array
    {
        return static::query()->first()?->toSettingsArray() ?? static::defaultsArray();
    }

    /**
     * Nilai bawaan lengkap (kolom database + rekening bank).
     */
    public static function defaultsArray(): array
    {
        return array_merge(static::COLUMN_DEFAULTS, static::BANK_DEFAULTS);
    }
}
