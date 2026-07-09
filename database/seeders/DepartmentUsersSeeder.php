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
        $firstCompany = Company::first();
        if (!$firstCompany) return;

        $depts = [
            ['slug_suffix' => 'contabilidad', 'email' => 'contabilidad@kuali.test', 'name' => 'Representante Contabilidad'],
            ['slug_suffix' => 'atencion-al-cliente-sac', 'email' => 'sac@kuali.test', 'name' => 'Representante SAC'],
        ];

        foreach ($depts as $d) {
            $dept = Department::where('company_id', $firstCompany->id)
                ->where('slug', $firstCompany->slug . '-' . $d['slug_suffix'])
                ->first();

            $user = User::updateOrCreate(
                ['email' => $d['email']],
                [
                    'name' => $d['name'],
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
