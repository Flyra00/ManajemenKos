<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Room::firstOrCreate
        (
            ['room_number' => 'A01'],
            [
                'floor'=> '1',
                'price'=> '1000000',
                'status'=> 'available',
                'is_active'=> 1,
                'description'=> 'Kamarnya Bagus',
            ]
        );
    }
}
