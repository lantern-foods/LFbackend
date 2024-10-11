<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if an admin user already exists by email or is_admin flag
        $default_user = User::where('email', 'admin@localhost')->orWhere('is_admin', 1)->first();

        if (empty($default_user)) {
            // Create the default admin user
            User::create([
                'name' => 'Administrator',
                'email' => env('ADMIN_EMAIL', 'admin@localhost'),
                'username' => env('ADMIN_USERNAME', 'admin'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin')),
                'is_admin' => 1,
                'is_active' => 1, // Ensure the user is active
            ]);
        }
    }
}
