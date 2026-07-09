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
        $companies = Company::all();

        foreach ($companies as $c) {
            $depts = Department::where('company_id', $c->id)->get();

            if ($depts->isEmpty()) continue;

            // 3 people per company
            $people = [
                ['first_name' => 'Carlos', 'last_name' => 'López', 'email' => 'carlos.' . $c->slug . '@example.com', 'phone' => '999000001'],
                ['first_name' => 'María', 'last_name' => 'García', 'email' => 'maria.' . $c->slug . '@example.com', 'phone' => '999000002'],
                ['first_name' => 'Juan', 'last_name' => 'Pérez', 'email' => 'juan.' . $c->slug . '@example.com', 'phone' => '999000003'],
            ];

            foreach ($people as $i => $p) {
                Person::create([
                    'first_name' => $p['first_name'],
                    'last_name' => $p['last_name'],
                    'email' => $p['email'],
                    'phone' => $p['phone'],
                    'company_id' => $c->id,
                    'department_id' => $depts[$i % $depts->count()]->id,
                    'is_active' => true,
                ]);
            }
        }
    }
}
