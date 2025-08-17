<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'demo@myshiotown.com'],
            [
                'name' => 'Demo User',
                'email' => 'demo@myshiotown.com',
                'password' => Hash::make('secret'),
                'email_verified_at' => now(),
            ]
        );
    }
}
