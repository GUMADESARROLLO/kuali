<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenance;
use App\Models\Company;
use App\Models\Person;
use App\Models\SoftwareLicense;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    private array $categories = [];
    private int $tagCounter = 1;

    public function run(): void
    {
        // Create categories
        $catNames = ['PC Desktop', 'Laptop', 'Mac/Apple', 'Monitor', 'Teclado', 'Mouse', 'Teléfono', 'Impresora', 'Switch', 'UPS'];
        foreach ($catNames as $i => $name) {
            $this->categories[$name] = AssetCategory::create([
                'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
                'sort_order' => $i, 'is_active' => true,
            ]);
        }

        $unimark = Company::where('slug', 'unimark-sa')->first();
        $guma = Company::where('slug', 'guma-pharma')->first();
        $innova = Company::where('slug', 'innova-industrias')->first();

        if ($unimark) $this->seedUnimark($unimark);
        if ($guma) $this->seedGumaPharma($guma);
        if ($innova) $this->seedInnova($innova);

        $this->seedLicenses($unimark);
    }

    private function person(string $name, Company $company): ?Person
    {
        $parts = explode(' ', $name, 2);
        $firstName = $parts[0] ?? $name;
        $lastName = $parts[1] ?? '';

        return Person::where('company_id', $company->id)
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->first();
    }

    private function nextTag(): string
    {
        return str_pad($this->tagCounter++, 6, '0', STR_PAD_LEFT);
    }

    private function createDesktop(string $name, ?Person $person, Company $company): Asset
    {
        $pc = Asset::create([
            'asset_tag' => $this->nextTag(),
            'name' => $name,
            'company_id' => $company->id,
            'asset_category_id' => $this->categories['PC Desktop']->id,
            'brand' => 'Lenovo',
            'model' => 'ThinkCentre M720',
            'serial_number' => 'SN-' . strtoupper(substr($company->slug, 0, 3)) . '-PC-' . $this->tagCounter,
            'status' => $person ? 'asignado' : 'disponible',
            'person_id' => $person?->id,
            'assigned_at' => $person ? Carbon::now() : null,
            'location' => 'Oficina',
            'purchase_date' => '2025-01-15',
            'purchase_cost' => 2800.00,
        ]);

        if ($person) {
            Asset::create([
                'asset_tag' => $this->nextTag(),
                'name' => "Monitor 24\" {$person->first_name}",
                'company_id' => $company->id,
                'asset_category_id' => $this->categories['Monitor']->id,
                'parent_asset_id' => $pc->id,
                'brand' => 'Dell', 'model' => 'P2419H',
                'serial_number' => 'SN-MON-' . $this->tagCounter,
                'status' => 'asignado', 'person_id' => $person->id,
                'assigned_at' => Carbon::now(),
            ]);
            Asset::create([
                'asset_tag' => $this->nextTag(),
                'name' => "Teclado USB",
                'company_id' => $company->id,
                'asset_category_id' => $this->categories['Teclado']->id,
                'parent_asset_id' => $pc->id,
                'brand' => 'Logitech', 'model' => 'K120',
                'serial_number' => 'SN-TEC-' . $this->tagCounter,
                'status' => 'asignado', 'person_id' => $person->id,
                'assigned_at' => Carbon::now(),
            ]);
            Asset::create([
                'asset_tag' => $this->nextTag(),
                'name' => "Mouse USB",
                'company_id' => $company->id,
                'asset_category_id' => $this->categories['Mouse']->id,
                'parent_asset_id' => $pc->id,
                'brand' => 'Logitech', 'model' => 'B100',
                'serial_number' => 'SN-MOU-' . $this->tagCounter,
                'status' => 'asignado', 'person_id' => $person->id,
                'assigned_at' => Carbon::now(),
            ]);
        }

        return $pc;
    }

    private function createLaptop(string $name, ?Person $person, Company $company, string $brand = 'HP', string $model = 'ProBook 450'): Asset
    {
        return Asset::create([
            'asset_tag' => $this->nextTag(),
            'name' => $name,
            'company_id' => $company->id,
            'asset_category_id' => $this->categories['Laptop']->id,
            'brand' => $brand, 'model' => $model,
            'serial_number' => 'SN-' . strtoupper(substr($company->slug, 0, 3)) . '-LAP-' . $this->tagCounter,
            'status' => $person ? 'asignado' : 'disponible',
            'person_id' => $person?->id,
            'assigned_at' => $person ? Carbon::now() : null,
            'location' => 'Oficina',
            'purchase_date' => '2025-06-01',
            'purchase_cost' => 4200.00,
            'warranty_expiry' => '2027-06-01',
        ]);
    }

    private function createMac(string $name, ?Person $person, Company $company): Asset
    {
        return Asset::create([
            'asset_tag' => $this->nextTag(),
            'name' => $name,
            'company_id' => $company->id,
            'asset_category_id' => $this->categories['Mac/Apple']->id,
            'brand' => 'Apple', 'model' => 'Mac Mini M2',
            'serial_number' => 'SN-' . strtoupper(substr($company->slug, 0, 3)) . '-MAC-' . $this->tagCounter,
            'status' => $person ? 'asignado' : 'disponible',
            'person_id' => $person?->id,
            'assigned_at' => $person ? Carbon::now() : null,
            'location' => 'Oficina',
            'purchase_date' => '2025-03-01',
            'purchase_cost' => 6500.00,
        ]);
    }

    private function createPhone(string $name, ?Person $person, Company $company): Asset
    {
        return Asset::create([
            'asset_tag' => $this->nextTag(),
            'name' => $name,
            'company_id' => $company->id,
            'asset_category_id' => $this->categories['Teléfono']->id,
            'brand' => 'Yealink', 'model' => 'T43U',
            'serial_number' => 'SN-' . strtoupper(substr($company->slug, 0, 3)) . '-TEL-' . $this->tagCounter,
            'status' => $person ? 'asignado' : 'disponible',
            'person_id' => $person?->id,
            'assigned_at' => $person ? Carbon::now() : null,
            'location' => 'Oficina',
            'purchase_date' => '2025-01-10',
            'purchase_cost' => 350.00,
        ]);
    }

    private function seedUnimark(Company $c): void
    {
        // GESTION HUMANA
        $anielka = $this->person('Anielka Gestión Humana', $c);
        $gh01 = $this->person('Puesto Gestión Humana 01', $c);
        $gh02 = $this->person('Puesto Gestión Humana 02', $c);

        $this->createLaptop('Laptop RRHH Anielka', $anielka, $c);
        $this->createPhone('Teléfono Anielka', $anielka, $c);
        $this->createDesktop('PC Gestión Humana 01', $gh01, $c);
        $this->createLaptop('Laptop GH 02', $gh02, $c);

        // REGENCIA
        $pedro = $this->person('Pedro Rueda', $c);
        $reg01 = $this->person('Puesto Regencia 01', $c);
        $reg02 = $this->person('Puesto Regencia 02', $c);

        $pcPedro = $this->createDesktop('PC Pedro Rueda', $pedro, $c);
        $this->createDesktop('PC Regencia 01', $reg01, $c);
        $this->createDesktop('PC Regencia 02', $reg02, $c);

        // Maintenance example
        AssetMaintenance::create([
            'asset_id' => $pcPedro->id,
            'maintenance_type' => 'reemplazo',
            'component' => 'fuente_poder',
            'description' => 'Reemplazo de fuente de poder por falla eléctrica.',
            'cost' => 180.00,
            'performed_by' => 'Soporte Técnico',
            'performed_at' => Carbon::parse('2025-09-20'),
        ]);

        // TESORERIA
        $willian = $this->person('Willian Otero', $c);
        $this->createDesktop('PC Willian Otero', $willian, $c);

        // JUDICIAL
        $grijalba = $this->person('Dr. Grijalba', $c);
        $josefa = $this->person('Dra. Josefa', $c);
        $this->createDesktop('PC Dr. Grijalba', $grijalba, $c);
        $this->createDesktop('PC Dra. Josefa', $josefa, $c);

        // ARTES
        $artes01 = $this->person('Puesto Artes 01', $c);
        $artes02 = $this->person('Puesto Artes 02', $c);
        $this->createMac('Mac Artes 01', $artes01, $c);
        $this->createDesktop('PC Artes 02', $artes02, $c);

        // INFORMATICA
        $jorge = $this->person('Jorge Morrison', $c);
        $maryan = $this->person('Maryan Espinoza', $c);
        $ariel = $this->person('Ariel Desarrollo', $c);

        $this->createLaptop('Laptop Jorge Morrison', $jorge, $c, 'Dell', 'Latitude 5540');
        $pcMaryan = $this->createDesktop('PC Maryan Espinoza', $maryan, $c);
        $this->createDesktop('PC Ariel Desarrollo', $ariel, $c);

        AssetMaintenance::create([
            'asset_id' => $pcMaryan->id,
            'maintenance_type' => 'reparacion',
            'component' => 'disco_duro',
            'description' => 'Reemplazo de disco por SSD Kingston 480GB.',
            'cost' => 320.00,
            'performed_by' => 'Soporte TI',
            'performed_at' => Carbon::parse('2026-05-15'),
        ]);

        // GERENTE GENERAL
        $francisco = $this->person('Francisco Sanches', $c);
        $pcFran = $this->createDesktop('PC Francisco Sanches', $francisco, $c);

        AssetMaintenance::create([
            'asset_id' => $pcFran->id,
            'maintenance_type' => 'preventivo',
            'component' => null,
            'description' => 'Limpieza general y cambio de pasta térmica.',
            'cost' => 60.00,
            'performed_by' => 'Soporte TI',
            'performed_at' => Carbon::parse('2025-12-01'),
        ]);
    }

    private function seedGumaPharma(Company $c): void
    {
        // REGENCIA
        $gp01 = $this->person('Puesto Regencia GP 01', $c);
        $gp02 = $this->person('Puesto Regencia GP 02', $c);
        $gp03 = $this->person('Puesto Regencia GP 03', $c);

        $this->createDesktop('PC Regencia GP 01', $gp01, $c);
        $this->createLaptop('Laptop Regencia GP 02', $gp02, $c);
        $this->createLaptop('Laptop Regencia GP 03', $gp03, $c);

        // MARCA
        $cristofer = $this->person('Cristofer Marca', $c);
        $this->createLaptop('Laptop Cristofer', $cristofer, $c);
        $this->createPhone('Teléfono Cristofer', $cristofer, $c);

        // DISEÑO
        $marcos = $this->person('Marcos Diseño', $c);
        $this->createDesktop('PC Marcos Diseño', $marcos, $c);
        $this->createPhone('Teléfono Marcos', $marcos, $c);

        // GERENCIA PAIS
        $fernando = $this->person('Fernando Matus', $c);
        $this->createLaptop('Laptop Fernando Matus', $fernando, $c, 'Dell', 'XPS 15');
        $this->createPhone('Teléfono Fernando', $fernando, $c);

        // GERENTE GENERAL
        $miguel = $this->person('Miguel Gutierres', $c);
        $this->createLaptop('Laptop Miguel Gutierres', $miguel, $c, 'Dell', 'XPS 15');
        $this->createMac('Mac Miguel Gutierres', $miguel, $c);
    }

    private function seedInnova(Company $c): void
    {
        // Just a few generic assets for Innova
        $people = Person::where('company_id', $c->id)->take(3)->get();
        if ($people->isNotEmpty()) {
            $this->createDesktop('PC Innova 01', $people->get(0), $c);
            if ($people->count() > 1) $this->createLaptop('Laptop Innova 01', $people->get(1), $c);
            if ($people->count() > 2) $this->createPhone('Teléfono Innova 01', $people->get(2), $c);
        }
    }

    private function seedLicenses(?Company $c): void
    {
        if (!$c) return;

        $office = SoftwareLicense::create([
            'company_id' => $c->id,
            'name' => 'Microsoft Office 2021 Professional Plus',
            'license_key' => 'O2021-UNIMARK-XXXXX',
            'vendor' => 'Microsoft',
            'seats_total' => 10,
            'seats_used' => 3,
            'purchase_date' => '2025-01-01',
            'expiry_date' => '2027-01-01',
            'cost' => 4500.00,
            'recurring' => false,
        ]);

        SoftwareLicense::create([
            'company_id' => $c->id,
            'name' => 'Windows 11 Pro',
            'vendor' => 'Microsoft',
            'seats_total' => 15,
            'seats_used' => 5,
            'purchase_date' => '2025-03-01',
            'cost' => 3200.00,
            'recurring' => false,
        ]);

        SoftwareLicense::create([
            'company_id' => $c->id,
            'name' => 'Antivirus Norton 365',
            'vendor' => 'NortonLifeLock',
            'seats_total' => 20,
            'seats_used' => 8,
            'purchase_date' => '2025-06-01',
            'expiry_date' => '2026-06-01',
            'cost' => 1800.00,
            'recurring' => true,
            'billing_period' => 'anual',
        ]);

        // Assign Office to 3 random PCs
        $assets = Asset::where('company_id', $c->id)->whereIn('asset_category_id', [
            $this->categories['PC Desktop']->id,
            $this->categories['Laptop']->id,
        ])->take(3)->get();

        foreach ($assets as $a) {
            $office->assets()->attach($a->id, ['installed_at' => Carbon::parse('2025-02-01')]);
        }
    }
}
