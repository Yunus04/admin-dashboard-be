<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MerchantSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all merchant users (exclude super_admin and admin)
        $merchantUsers = User::where('role', 'merchant')->get();

        foreach ($merchantUsers as $index => $merchantUser) {
            $merchantNumber = $index + 1;
            Merchant::updateOrCreate(
                ['user_id' => $merchantUser->id],
                [
                    'business_name' => "Merchant Store {$merchantNumber}",
                    'phone' => '+62812345678' . str_pad($merchantNumber, 2, '0', STR_PAD_LEFT),
                    'address' => "{$merchantNumber} Commerce Street, Business District, City {$merchantNumber}",
                ]
            );
        }
    }
}

