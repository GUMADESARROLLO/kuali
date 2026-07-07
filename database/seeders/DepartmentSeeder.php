<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Recursos Humanos', 'description' => 'Gestion de personal y nomina'],
            ['name' => 'Contabilidad', 'description' => 'Finanzas, cuentas por pagar y cobrar'],
            ['name' => 'Ventas', 'description' => 'Fuerza comercial y atencion a clientes'],
            ['name' => 'Marketing', 'description' => 'Comunicacion y publicidad'],
            ['name' => 'Operaciones', 'description' => 'Logistica y cadena de suministro'],
            ['name' => 'Legal', 'description' => 'Asuntos juridicos y contractuales'],
            ['name' => 'Administracion', 'description' => 'Servicios generales y mantenimiento'],
            ['name' => 'Tecnologia', 'description' => 'Desarrollo y operaciones de TI'],
        ];

        foreach ($departments as $row) {
            Department::updateOrCreate(
                ['slug' => Str::slug($row['name'])],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'is_active' => true,
                ],
            );
        }
    }
}
