<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            ['name' => 'Ariel', 'email' => 'ariel@kuali.test'],
            ['name' => 'Maryan', 'email' => 'maryan@kuali.test'],
            ['name' => 'Jorge', 'email' => 'jorge@kuali.test'],
        ];

        foreach ($agents as $a) {
            $user = User::updateOrCreate(
                ['email' => $a['email']],
                [
                    'name' => $a['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
            $user->syncRoles(['agente_it']);
        }
    }
}
