<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
        	'name' => \Str::random(5),
        	'email' => \Str::random(7).'@aspire.com',
        	'password' => bcrypt(123456)
        ]);
    }
}
