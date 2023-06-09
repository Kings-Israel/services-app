<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin1 = [
            'first_name' => 'Services',
            'last_name' => 'Admin',
            'email' => 'admin@services.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $admin2 = [
            'first_name' => 'Deveint',
            'last_name' => 'Admin',
            'email' => 'admin@deveint.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        User::create($admin1)->assignRole('admin');
        User::create($admin2)->assignRole('admin');
    }
}
