<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $moderator = Role::create(['name' => 'moderator']);
        $reader = Role::create(['name' => 'reader']);

        User::create([
            'name' => 'Модератор',
            'email' => 'moderator@example.com',
            'password' => Hash::make('password'),
            'role_id' => $moderator->id,
        ]);
    }
}
