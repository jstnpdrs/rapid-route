<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'role' => 'Administrator',
            'password' => Hash::make('admin'),
            'status' => 'Approved',
        ]);
        User::factory(100)->create();
    }
}
