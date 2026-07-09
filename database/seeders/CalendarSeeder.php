<?php

namespace Database\Seeders;

use App\Models\Calendar;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    public function run(): void
    {
        Calendar::create([
            'name' => 'Horario Oficina',
            'description' => 'Lun-Vie 7:30-18:00, Sáb 8:00-13:00, Dom cerrado',
            'is_active' => true,
            'hours' => [
                'mon' => ['enabled' => true, 'start' => '07:30', 'end' => '18:00'],
                'tue' => ['enabled' => true, 'start' => '07:30', 'end' => '18:00'],
                'wed' => ['enabled' => true, 'start' => '07:30', 'end' => '18:00'],
                'thu' => ['enabled' => true, 'start' => '07:30', 'end' => '18:00'],
                'fri' => ['enabled' => true, 'start' => '07:30', 'end' => '18:00'],
                'sat' => ['enabled' => true, 'start' => '08:00', 'end' => '13:00'],
                'sun' => ['enabled' => false, 'start' => '00:00', 'end' => '00:00'],
            ],
        ]);
    }
}
