<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'user',
                'email' => 'user@example.com',
                'password' => Hash::make('user1234'),
                'email_verified_at' => now(),
                'role' => 0,
            ],
            [
                'name' => 'general',
                'email' => 'general@example.com',
                'password' => Hash::make('general1234'),
                'email_verified_at' => now(),
                'role' => 0,
            ],
            [
                'name' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin1234'),
                'email_verified_at' => now(),
                'role' => 1,
            ],
        ]);
    }
}
