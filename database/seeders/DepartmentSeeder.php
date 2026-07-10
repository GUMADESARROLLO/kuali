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
        // Unimark S.A. departments
        $unimark = Company::where('slug', 'unimark-sa')->first();
        if ($unimark) {
            $depts = [
                'Gestion Humana',
                'Regencia',
                'Tesorería',
                'Judicial',
                'Artes',
                'Informatica',
                'Gerente General',
            ];
            foreach ($depts as $name) {
                Department::create([
                    'company_id' => $unimark->id,
                    'name' => $name,
                    'slug' => Str::slug($unimark->name . '-' . $name),
                    'description' => 'Departamento de ' . $name . ' - ' . $unimark->name,
                    'is_active' => true,
                ]);
            }
        }

        // Guma Pharma departments
        $guma = Company::where('slug', 'guma-pharma')->first();
        if ($guma) {
            $depts = [
                'Regencia',
                'Marca',
                'Diseño',
                'Gerencia Pais',
                'Gerente General',
            ];
            foreach ($depts as $name) {
                Department::create([
                    'company_id' => $guma->id,
                    'name' => $name,
                    'slug' => Str::slug($guma->name . '-' . $name),
                    'description' => 'Departamento de ' . $name . ' - ' . $guma->name,
                    'is_active' => true,
                ]);
            }
        }

        // Innova Industrias — keep generic departments
        $innova = Company::where('slug', 'innova-industrias')->first();
        if ($innova) {
            $generic = [
                'Recursos Humanos',
                'Contabilidad',
                'Ventas',
                'Operaciones',
                'Tecnologia',
                'Atencion al Cliente (SAC)',
            ];
            foreach ($generic as $name) {
                Department::create([
                    'company_id' => $innova->id,
                    'name' => $name,
                    'slug' => Str::slug($innova->name . '-' . $name),
                    'description' => 'Departamento de ' . $name . ' - ' . $innova->name,
                    'is_active' => true,
                ]);
            }
        }
    }
}
