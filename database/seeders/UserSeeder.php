<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );

        // Admin
        User::updateOrCreate(
            ['email' => 'admin2@admin.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        for ($i = 1; $i <= 48; $i++) {
            User::updateOrCreate(
                ['email' => "merchant{$i}@merchant.com"],
                [
                    'name' => "Merchant Store {$i}",
                    'password' => Hash::make('password'),
                    'role' => 'merchant',
                ]
            );
        }
    }
}
