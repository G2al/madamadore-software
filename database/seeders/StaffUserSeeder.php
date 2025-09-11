<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'staff@gmail.com'], // cambia email
            [
                'name' => 'Staff Demo',
                'password' => Hash::make('staff123'), // cambia password
                'role' => 'staff',
            ]
        );
    }
}
