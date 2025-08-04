<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            "name" => "Administrator",
            "email" => "admin@gamereviews.com",
            "password" => Hash::make("admin123"),
            "role" => "admin",
            "email_verified_at" => now(),
        ]);

        echo "Admin user created: admin@gamereviews.com / admin123\n";
    }
}