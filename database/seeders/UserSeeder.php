<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'firstname' =>      'Admin',
                'lastname' =>      'User',
                'email'     =>      'admin@gmail.com',
                'user_type' =>      1,
                'password'  =>      Hash::make(123456),
                'status'    =>      1,
            ],
        ];

        User::insert($users);
    }
}
