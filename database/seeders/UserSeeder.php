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
        $users = [
            ['name' => 'Anna Peeters',   'email' => 'admin@canteen.test',    'admin' => true,  'active' => true],
            ['name' => 'Tom Willems',    'email' => 'manager@canteen.test',  'admin' => true,  'active' => true],
            ['name' => 'Sofie Janssens', 'email' => 'sofie@canteen.test',    'admin' => false, 'active' => true],
            ['name' => 'Lucas De Smet',  'email' => 'lucas@canteen.test',    'admin' => false, 'active' => true],
            ['name' => 'Emma Claes',     'email' => 'emma@canteen.test',     'admin' => false, 'active' => true],
            ['name' => 'Jonas Maes',     'email' => 'jonas@canteen.test',    'admin' => false, 'active' => false],
        ];

        foreach ($users as $user) {
            User::create([
                ...$user,
                'password' => Hash::make('user1234'),
                'email_verified_at' => now(),
            ]);
        }
    }
}
