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
        $admin = Role::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'=> 'Admin_rafly',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('admin');

        $caretaker = Role::firstOrCreate(
            ['email'=> 'caretaker'],
            [
                'name' => 'Caretaker_rafly',
                'phone'=> '081234567891',
                'password'=> Hash::make('password'),
            ]
        );

        $caretaker->assignRole('caretaker');
    }
}
