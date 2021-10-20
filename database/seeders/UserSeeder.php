<?php

namespace Database\Seeders;

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
        \DB::table('users')->insert([
            'name' => 'Admin G. Admin',
            'email' => 'adming@gmail.com',
            'password' => \Hash::make('12121212'),
        ]);
    }
}
