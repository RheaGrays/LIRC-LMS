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
            ['email' => 'admin@corjesucollege.edu.ph'],
            [
                'password'        => Hash::make('lems2026'),
                'full_name'       => 'LEMS Administrator',
                'role'            => 'Super Admin',
                'avatar_initials' => 'LA',
                'is_active'       => true,
            ]
        );
    }
}
