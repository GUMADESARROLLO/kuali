<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Person;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{
    public function run(): void
    {
        $unimark = Company::where('slug', 'unimark-sa')->first();
        $guma = Company::where('slug', 'guma-pharma')->first();

        // ---- Unimark S.A. ----
        if ($unimark) {
            $dept = fn(string $name) => Department::where('company_id', $unimark->id)->where('name', $name)->first();

            $people = [
                ['first_name' => 'Anielka',          'last_name' => 'Gestión Humana',  'email' => 'anielka@unimark.com',     'department' => 'Gestion Humana'],
                ['first_name' => 'Pedro',            'last_name' => 'Rueda',           'email' => 'pedro.rueda@unimark.com', 'department' => 'Regencia'],
                ['first_name' => 'Willian',          'last_name' => 'Otero',           'email' => 'willian.otero@unimark.com', 'department' => 'Tesorería'],
                ['first_name' => 'Dr.',              'last_name' => 'Grijalba',        'email' => 'grijalba@unimark.com',    'department' => 'Judicial'],
                ['first_name' => 'Dra.',             'last_name' => 'Josefa',          'email' => 'josefa@unimark.com',      'department' => 'Judicial'],
                ['first_name' => 'Jorge',            'last_name' => 'Morrison',        'email' => 'jorge@unimark.com',       'department' => 'Informatica'],
                ['first_name' => 'Maryan',           'last_name' => 'Espinoza',        'email' => 'maryan@unimark.com',      'department' => 'Informatica'],
                ['first_name' => 'Ariel',            'last_name' => 'Desarrollo',       'email' => 'ariel@unimark.com',       'department' => 'Informatica'],
                ['first_name' => 'Francisco',        'last_name' => 'Sanches',         'email' => 'francisco@unimark.com',   'department' => 'Gerente General'],
                // Department-level people (puestos sin persona específica)
                ['first_name' => 'Puesto',           'last_name' => 'Gestión Humana 01','email' => 'gh01@unimark.com',        'department' => 'Gestion Humana'],
                ['first_name' => 'Puesto',           'last_name' => 'Gestión Humana 02','email' => 'gh02@unimark.com',        'department' => 'Gestion Humana'],
                ['first_name' => 'Puesto',           'last_name' => 'Regencia 01',      'email' => 'regencia01@unimark.com',  'department' => 'Regencia'],
                ['first_name' => 'Puesto',           'last_name' => 'Regencia 02',      'email' => 'regencia02@unimark.com',  'department' => 'Regencia'],
                ['first_name' => 'Puesto',           'last_name' => 'Artes 01',         'email' => 'artes01@unimark.com',     'department' => 'Artes'],
                ['first_name' => 'Puesto',           'last_name' => 'Artes 02',         'email' => 'artes02@unimark.com',     'department' => 'Artes'],
            ];

            foreach ($people as $p) {
                Person::create([
                    'first_name' => $p['first_name'],
                    'last_name' => $p['last_name'],
                    'email' => $p['email'],
                    'phone' => '505-' . random_int(8000, 8999) . '-' . random_int(1000, 9999),
                    'company_id' => $unimark->id,
                    'department_id' => $dept($p['department'])?->id,
                    'is_active' => true,
                ]);
            }
        }

        // ---- Guma Pharma ----
        if ($guma) {
            $dept = fn(string $name) => Department::where('company_id', $guma->id)->where('name', $name)->first();

            $people = [
                ['first_name' => 'Puesto',           'last_name' => 'Regencia GP 01',   'email' => 'gp01@gumapharma.com',     'department' => 'Regencia'],
                ['first_name' => 'Puesto',           'last_name' => 'Regencia GP 02',   'email' => 'gp02@gumapharma.com',     'department' => 'Regencia'],
                ['first_name' => 'Puesto',           'last_name' => 'Regencia GP 03',   'email' => 'gp03@gumapharma.com',     'department' => 'Regencia'],
                ['first_name' => 'Cristofer',        'last_name' => 'Marca',            'email' => 'cristofer@gumapharma.com','department' => 'Marca'],
                ['first_name' => 'Marcos',           'last_name' => 'Diseño',           'email' => 'marcos@gumapharma.com',   'department' => 'Diseño'],
                ['first_name' => 'Fernando',         'last_name' => 'Matus',            'email' => 'fernando@gumapharma.com',  'department' => 'Gerencia Pais'],
                ['first_name' => 'Miguel',           'last_name' => 'Gutierres',        'email' => 'miguel@gumapharma.com',   'department' => 'Gerente General'],
            ];

            foreach ($people as $p) {
                Person::create([
                    'first_name' => $p['first_name'],
                    'last_name' => $p['last_name'],
                    'email' => $p['email'],
                    'phone' => '505-' . random_int(8000, 8999) . '-' . random_int(1000, 9999),
                    'company_id' => $guma->id,
                    'department_id' => $dept($p['department'])?->id,
                    'is_active' => true,
                ]);
            }
        }

        // ---- Innova Industrias ----
        $innova = Company::where('slug', 'innova-industrias')->first();
        if ($innova) {
            $dept = fn(string $name) => Department::where('company_id', $innova->id)->where('name', $name)->first();

            $people = [
                ['first_name' => 'Carlos',   'last_name' => 'López',   'email' => 'carlos.lopez@innova.com',  'department' => 'Tecnologia'],
                ['first_name' => 'María',    'last_name' => 'García',  'email' => 'maria.garcia@innova.com',  'department' => 'Contabilidad'],
                ['first_name' => 'Juan',     'last_name' => 'Pérez',   'email' => 'juan.perez@innova.com',    'department' => 'Operaciones'],
            ];

            foreach ($people as $p) {
                Person::create([
                    'first_name' => $p['first_name'],
                    'last_name' => $p['last_name'],
                    'email' => $p['email'],
                    'phone' => '505-' . random_int(8000, 8999) . '-' . random_int(1000, 9999),
                    'company_id' => $innova->id,
                    'department_id' => $dept($p['department'])?->id,
                    'is_active' => true,
                ]);
            }
        }
    }
}
