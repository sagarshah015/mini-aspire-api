<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::create([
        	'email' => 'admin'.\Str::random(3).'@aspire.com',
        	'password' => bcrypt(123456)
        ]);
    }
}
