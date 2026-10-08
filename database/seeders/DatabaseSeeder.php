<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Sabaf user
        User::create([
            'name' => 'Sabaf',
            'email' => 'admin@library.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        // Create Librarian user
        User::create([
            'name' => 'John Librarian',
            'email' => 'librarian@library.com',
            'password' => Hash::make('password'),
            'role' => 'librarian'
        ]);

        // Create regular users
        User::create([
            'name' => 'Alice Student',
            'email' => 'alice@student.com',
            'password' => Hash::make('password'),
            'role' => 'user'
        ]);

        User::create([
            'name' => 'Bob Member',
            'email' => 'bob@member.com',
            'password' => Hash::make('password'),
            'role' => 'user'
        ]);

        User::create([
            'name' => 'Carol Reader',
            'email' => 'carol@reader.com',
            'password' => Hash::make('password'),
            'role' => 'user'
        ]);

        // Seed books
        $this->call(BookSeeder::class);
    }
}
