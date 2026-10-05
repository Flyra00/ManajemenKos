<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;




class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'=> 'AdminRafly',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->forceFill(['email_verified_at' => now()])->save();
        $admin->assignRole('admin');

        $owner = User::firstOrCreate(
            ['email'=> 'owner@gmail.com'],
            [
                'name' => 'OwnerRafly',
                'phone'=> '081234567891',
                'password'=> Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $owner->forceFill(['email_verified_at' => now()])->save();
        $owner->assignRole('owner');

        $staff = User::firstOrCreate(
            ['email'=> 'staff@gmail.com'],
            [
                'name' => 'StaffKos',
                'phone'=> '081234567892',
                'password'=> Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $staff->forceFill(['email_verified_at' => now()])->save();
        $staff->assignRole('staff');
    }
}
