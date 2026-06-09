<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'chris@diraddo.com')->doesntExist()) {
            User::create([
                'name'         => 'Chris Diraddo',
                'email'        => 'chris@diraddo.com',
                'password'     => Hash::make('changeme123!'),
                'role'         => 'admin',
                'avatar_color' => '#6366f1',
            ]);

            $this->command->info('Admin user created: alex@securexfilings.com / changeme123!');
            $this->command->warn('Change this password immediately after first login!');
        }
    }
}
