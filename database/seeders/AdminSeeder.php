<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // The model hashes the password. Re-running restores the training credentials.
        Admin::updateOrCreate(['login' => 'admin'], ['password' => 'admin']);
    }
}
