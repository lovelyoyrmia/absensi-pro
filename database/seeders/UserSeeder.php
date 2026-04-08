<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nip' => 'ADM-001',
            'name' => 'System Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $employees = [
            ['name' => 'John Doe', 'email' => 'john@test.com'],
            ['name' => 'Jane Smith', 'email' => 'jane@test.com'],
            ['name' => 'Budi Santoso', 'email' => 'budi@test.com'],
        ];

        foreach ($employees as $emp) {
            User::create([
                'nip' => 'EMP-' . date('Y') . '-' . rand(1000, 9999),
                'name' => $emp['name'],
                'email' => $emp['email'],
                'password' => Hash::make('password'),
                'role' => 'employee',
            ]);
        }
    }
}
