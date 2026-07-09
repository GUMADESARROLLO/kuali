<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $deptNames = [
            'Recursos Humanos',
            'Contabilidad',
            'Ventas',
            'Marketing',
            'Operaciones',
            'Legal',
            'Administracion',
            'Tecnologia',
            'Atencion al Cliente (SAC)',
        ];

        $companies = Company::all();

        foreach ($companies as $company) {
            foreach ($deptNames as $name) {
                Department::updateOrCreate(
                    ['company_id' => $company->id, 'slug' => Str::slug($company->name . '-' . $name)],
                    [
                        'name' => $name,
                        'description' => 'Departamento de ' . $name . ' - ' . $company->name,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
