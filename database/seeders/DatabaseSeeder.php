<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'nim' => '2512501491',
            'role' => 'spv inventory',
            'username' => 'ali',
            'password' => Hash::make('123'),
            'email' => 'test@example.com',


        ]);
    }
}
