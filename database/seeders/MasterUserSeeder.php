<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterUser;
use Illuminate\Support\Facades\Hash;

class MasterUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default super admin
        MasterUser::firstOrCreate(
            ['email' => 'admin@seoom.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
            ]
        );

        // Create or update master user
        MasterUser::updateOrCreate(
            ['email' => 'master@seoom.com'],
            [
                'name' => 'Master Admin',
                'password' => Hash::make('Qkqh090909!'),
                'role' => 'super_admin',
            ]
        );

        $this->command->info('Master user created: admin@seoom.com / admin123');
        $this->command->info('Master user created/updated: master@seoom.com / Qkqh090909!');
    }
}







