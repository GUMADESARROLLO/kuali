<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DepartmentUsersSeeder extends Seeder
{
    public function run(): void
    {
        $innova = Company::where('slug', 'innova-industrias')->first();
        if (!$innova) return;

        $deptNames = [
            ['name' => 'Contabilidad', 'email' => 'contabilidad@kuali.test', 'display' => 'Representante Contabilidad'],
            ['name' => 'Atencion al Cliente (SAC)', 'email' => 'sac@kuali.test', 'display' => 'Representante SAC'],
        ];

        foreach ($deptNames as $d) {
            $dept = Department::where('company_id', $innova->id)
                ->where('name', $d['name'])
                ->first();

            $user = User::updateOrCreate(
                ['email' => $d['email']],
                [
                    'name' => $d['display'],
                    'password' => Hash::make('password'),
                    'department_id' => $dept?->id,
                    'phone' => '+10000000' . random_int(100, 999),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
            $user->syncRoles(['usuario']);
        }
    }
}
