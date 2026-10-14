<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * Determine if the user has verified their email address.
     *
     * Status diambil dari database: kolom `email_verified_at`, atau peran
     * pengelola (admin/owner/staff) yang juga dibaca dari database (tabel peran
     * Spatie). Akun pengelola tidak diwajibkan verifikasi email karena hanya
     * registrasi penyewa publik yang memerlukannya.
     *
     * Daftar email hardcoded (admin@gmail.com/owner@gmail.com/staff@gmail.com)
     * dihapus: itu backdoor yang membuat siapa pun yang mendaftar dengan alamat
     * tersebut otomatis dianggap terverifikasi.
     */
    public function hasVerifiedEmail(): bool
    {
        if (! is_null($this->email_verified_at)) {
            return true;
        }

        try {
            return $this->hasAnyRole(['admin', 'owner', 'staff']);
        } catch (\Throwable $e) {
            // Abaikan bila peran belum termuat atau migrasi sedang berjalan.
            return false;
        }
    }



    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }

    public function verifiedPayment()
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    public function handledMaintenance()
    {
        return $this->hasMany(MaintenanceRequest::class,'handled_by');
    }

    public function createdExpenses()
    {
        return $this->hasMany(Expense::class,'created_by');
    }
}
