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
    public function run(): void
    {
        // Create categories
        $catNames = ['PC Desktop', 'Laptop', 'Monitor', 'Teclado', 'Mouse', 'Teléfono', 'Impresora', 'Switch', 'UPS', 'Tablet'];
        $categories = [];
        foreach ($catNames as $i => $name) {
            $cat = AssetCategory::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'sort_order' => $i, 'is_active' => true]);
            $categories[$name] = $cat;
        }

        $companies = Company::all();
        $people = Person::where('is_active', true)->get();

        if ($companies->isEmpty() || $people->isEmpty()) return;

        // Unimark S.A assets
        $guma = $companies->firstWhere('slug', 'unimark-sa');

        // PC Desktop with components
        $pc1 = Asset::create([
            'asset_tag' => '000001',
            'name' => 'PC Escritorio Gerencia',
            'company_id' => $guma->id,
            'asset_category_id' => $categories['PC Desktop']->id,
            'brand' => 'Lenovo',
            'model' => 'ThinkCentre M720',
            'serial_number' => 'SN-UNI-001',
            'status' => 'asignado',
            'person_id' => $people[0]->id ?? null,
            'assigned_at' => Carbon::now(),
            'location' => 'Oficina Gerencia - Piso 2',
            'purchase_date' => '2025-03-15',
            'purchase_cost' => 2800.00,
            'warranty_expiry' => '2027-03-15',
        ]);

        Asset::create([
            'asset_tag' => '000002',
            'name' => 'Monitor 24"',
            'company_id' => $guma->id,
            'asset_category_id' => $categories['Monitor']->id,
            'parent_asset_id' => $pc1->id,
            'brand' => 'Dell',
            'model' => 'P2419H',
            'serial_number' => 'SN-MON-UNI-001',
            'status' => 'asignado',
            'person_id' => $people[0]->id ?? null,
            'assigned_at' => Carbon::now(),
        ]);

        Asset::create([
            'asset_tag' => '000003',
            'name' => 'Teclado USB',
            'company_id' => $guma->id,
            'asset_category_id' => $categories['Teclado']->id,
            'parent_asset_id' => $pc1->id,
            'brand' => 'Logitech',
            'model' => 'K120',
            'status' => 'asignado',
            'person_id' => $people[0]->id ?? null,
            'assigned_at' => Carbon::now(),
        ]);

        Asset::create([
            'asset_tag' => '000004',
            'name' => 'Mouse USB',
            'company_id' => $guma->id,
            'asset_category_id' => $categories['Mouse']->id,
            'parent_asset_id' => $pc1->id,
            'brand' => 'Logitech',
            'model' => 'B100',
            'status' => 'asignado',
            'person_id' => $people[0]->id ?? null,
            'assigned_at' => Carbon::now(),
        ]);

        // Laptop
        $lap1 = Asset::create([
            'asset_tag' => '000005',
            'name' => 'Laptop Contabilidad',
            'company_id' => $guma->id,
            'asset_category_id' => $categories['Laptop']->id,
            'brand' => 'HP',
            'model' => 'ProBook 450 G10',
            'serial_number' => 'SN-LAP-UNI-001',
            'status' => 'asignado',
            'person_id' => $people[0]->id ?? null,
            'assigned_at' => Carbon::now(),
            'location' => 'Oficina Contabilidad - Piso 1',
            'purchase_date' => '2025-06-01',
            'purchase_cost' => 4200.00,
            'warranty_expiry' => '2027-06-01',
        ]);

        // Phone
        Asset::create([
            'asset_tag' => '000006',
            'name' => 'Teléfono IP Recepción',
            'company_id' => $guma->id,
            'asset_category_id' => $categories['Teléfono']->id,
            'brand' => 'Yealink',
            'model' => 'T43U',
            'serial_number' => 'SN-TEL-UNI-001',
            'status' => 'asignado',
            'person_id' => $people[0]->id ?? null,
            'assigned_at' => Carbon::now(),
            'location' => 'Recepción - Piso 1',
            'purchase_date' => '2025-01-10',
            'purchase_cost' => 350.00,
        ]);

        // Maintenance records for PC
        AssetMaintenance::create([
            'asset_id' => $pc1->id,
            'maintenance_type' => 'reemplazo',
            'component' => 'fuente_poder',
            'description' => 'Reemplazo de fuente de poder por falla eléctrica. Se instaló fuente Corsair 550W.',
            'cost' => 180.00,
            'performed_by' => 'Técnico externo - ServiPC',
            'performed_at' => Carbon::parse('2025-09-20 14:30:00'),
            'notes' => 'La fuente original explotó por sobrecarga',
        ]);

        AssetMaintenance::create([
            'asset_id' => $pc1->id,
            'maintenance_type' => 'preventivo',
            'component' => null,
            'description' => 'Limpieza general, cambio de pasta térmica y soplado de ventiladores.',
            'cost' => 60.00,
            'performed_by' => 'Soporte interno',
            'performed_at' => Carbon::parse('2025-12-01 09:00:00'),
        ]);

        // Maintenance for laptop
        AssetMaintenance::create([
            'asset_id' => $lap1->id,
            'maintenance_type' => 'reemplazo',
            'component' => 'bateria',
            'description' => 'Batería original no retenía carga. Reemplazo por batería compatible HP.',
            'cost' => 250.00,
            'performed_by' => 'Soporte interno',
            'performed_at' => Carbon::parse('2026-02-10 11:00:00'),
            'notes' => 'Batería con 2 años de uso, aprox 300 ciclos',
        ]);

        AssetMaintenance::create([
            'asset_id' => $pc1->id,
            'maintenance_type' => 'reparacion',
            'component' => 'disco_duro',
            'description' => 'Disco duro con sectores defectuosos. Reemplazo por SSD Kingston 480GB.',
            'cost' => 320.00,
            'performed_by' => 'Soporte interno',
            'performed_at' => Carbon::parse('2026-05-15 15:00:00'),
        ]);

        // Software licenses
        $office = SoftwareLicense::create([
            'company_id' => $guma->id,
            'name' => 'Microsoft Office 2021 Professional Plus',
            'license_key' => 'XXXXX-XXXXX-XXXXX-XXXXX-XXXXX',
            'vendor' => 'Microsoft',
            'seats_total' => 10,
            'seats_used' => 2,
            'purchase_date' => '2025-01-01',
            'expiry_date' => '2027-01-01',
            'cost' => 4500.00,
            'notes' => 'Licencia por volumen',
        ]);

        SoftwareLicense::create([
            'company_id' => $guma->id,
            'name' => 'Windows 11 Pro',
            'license_key' => 'W11-XXXXX-XXXXX',
            'vendor' => 'Microsoft',
            'seats_total' => 15,
            'seats_used' => 5,
            'purchase_date' => '2025-03-01',
            'cost' => 3200.00,
        ]);

        SoftwareLicense::create([
            'company_id' => $guma->id,
            'name' => 'Antivirus Norton 365',
            'license_key' => 'NORTON-365-UNI-001',
            'vendor' => 'NortonLifeLock',
            'seats_total' => 20,
            'seats_used' => 3,
            'purchase_date' => '2025-06-01',
            'expiry_date' => '2026-06-01',
            'cost' => 1800.00,
        ]);

        // Assign licenses to PC
        $office->assets()->attach($pc1->id, ['installed_at' => '2025-03-20 10:00:00']);
        $office->assets()->attach($lap1->id, ['installed_at' => '2025-06-05 14:00:00']);
    }
}



