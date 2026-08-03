<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    //
    protected $fillable=[
        'user_id',
        'ktp_number',
        'emergency_name',
        'emergency_phone',
        'job',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leases(){
        return $this->hasMany(Lease::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
