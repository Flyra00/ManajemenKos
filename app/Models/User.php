<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
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
        'phone',
    ];


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
