<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $itDepartment = Department::where('slug', 'tecnologia')->first()
            ?? Department::first();

        $admin = User::updateOrCreate(
            ['email' => 'admin@kuali.test'],
            [
                'name' => 'Admin IT',
                'password' => Hash::make('password'),
                'department_id' => $itDepartment?->id,
                'phone' => '+10000000000',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['admin_it']);

        $agent = User::updateOrCreate(
            ['email' => 'agente@kuali.test'],
            [
                'name' => 'Agente IT',
                'password' => Hash::make('password'),
                'department_id' => $itDepartment?->id,
                'phone' => '+10000000001',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
        $agent->syncRoles(['agente_it']);

        $user = User::updateOrCreate(
            ['email' => 'usuario@kuali.test'],
            [
                'name' => 'Representante RRHH',
                'password' => Hash::make('password'),
                'department_id' => Department::where('slug', 'recursos-humanos')->first()?->id,
                'phone' => '+10000000002',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
        $user->syncRoles(['usuario']);
    }
}
