<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Flavio',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'remember_token' => Str::random(10),
        ]);

        $this->call([
            OrderSeeder::class,
        ]);
    }
}
