<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

         \App\Models\User::factory()->create([
             'name' => 'John',
             'last_name' => 'Doe',
             'password' => 'password',
             'email' => 'test@example.com',
         ]);
    }
}
