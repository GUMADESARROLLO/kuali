<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Hardware',
                'description' => 'Equipos fisicos y perifericos',
                'subcategories' => [
                    'Computadora de escritorio',
                    'Laptop',
                    'Impresora',
                    'Monitor',
                    'Perifericos (teclado, mouse, audifonos)',
                    'Telefono de oficina',
                ],
            ],
            [
                'name' => 'Software',
                'description' => 'Aplicaciones, sistema operativo y licencias',
                'subcategories' => [
                    'Instalacion de software',
                    'Actualizacion / parches',
                    'Licencias y activacion',
                    'Errores de aplicacion',
                    'Sistema operativo',
                    'Antivirus / seguridad',
                ],
            ],
            [
                'name' => 'Redes y conectividad',
                'description' => 'Acceso a internet, WiFi, VPN, cableado',
                'subcategories' => [
                    'WiFi',
                    'VPN',
                    'Cableado / puntos de red',
                    'Acceso a internet',
                    'Servidores internos',
                ],
            ],
            [
                'name' => 'Correo electronico',
                'description' => 'Cuentas, envio, recepcion, configuracion',
                'subcategories' => [
                    'Configuracion de cuenta',
                    'Contraseña / recuperacion',
                    'Buzon lleno',
                    'Spam / phishing',
                ],
            ],
            [
                'name' => 'Cuentas y accesos',
                'description' => 'Altas, bajas, permisos, autenticacion',
                'subcategories' => [
                    'Alta de usuario',
                    'Baja de usuario',
                    'Cambio de contraseña',
                    'Permisos a sistemas',
                    'Bloqueo de cuenta',
                    'Doble factor (2FA)',
                ],
            ],
        ];

        foreach ($data as $i => $cat) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );

            foreach ($cat['subcategories'] as $j => $subName) {
                Subcategory::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'slug' => Str::slug($subName),
                    ],
                    [
                        'name' => $subName,
                        'is_active' => true,
                        'sort_order' => $j,
                    ],
                );
            }
        }
    }
}
