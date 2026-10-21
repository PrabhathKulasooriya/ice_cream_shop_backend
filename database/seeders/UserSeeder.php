<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Shop Owner', 'email' => 'owner@icecream.test', 'role' => 'owner'],
            ['name' => 'Shop Manager', 'email' => 'manager@icecream.test', 'role' => 'manager'],
            ['name' => 'Shop Cashier', 'email' => 'cashier@icecream.test', 'role' => 'cashier'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('123456'),
                    'role' => $userData['role'],
                ]
            );
        }
    }
}
