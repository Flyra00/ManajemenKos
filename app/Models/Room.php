<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    //
    protected $fillable = [
        "room_number",
        "floor",
        "price",
        "status",
        "is_active",
        "description",
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function facilities()
    {
        return $this->belongsToMany(Facilitie::class);
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
