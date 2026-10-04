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
        "image",
        "description",
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function facilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'room_facilities'
        );
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease()
    {
        return $this->hasOne(Lease::class)->where('status', 'active')->latestOfMany();
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function getPricePerMonthAttribute()
    {
        return $this->price;
    }
}

