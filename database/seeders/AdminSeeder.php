<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@run.edu.ng'],
            [
                'surname' => 'Admin',
                'firstname' => 'System',
                'othername' => null,
                'phone' => '08000000000',
                'password' => Hash::make('default123'),
                'role' => '300',
                'account_status' => 'ACTIVE',
                'title' => 'Mr',
            ]
        );

        Admin::firstOrCreate(
            ['email' => 'recommender@run.edu.ng'],
            [
                'surname' => 'Recommender',
                'firstname' => 'Test',
                'othername' => null,
                'phone' => '08000000001',
                'password' => Hash::make('default123'),
                'role' => '200',
                'account_status' => 'ACTIVE',
                'title' => 'Mrs',
            ]
        );
    }
}
