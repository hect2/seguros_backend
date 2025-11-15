<?php

namespace Database\Seeders;

use App\Models\Critical;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CriticalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $criticals = [
            [
                'name' => 'Baja',
                'slug' => 'low',
                'description' => 'Incidente menor con impacto mínimo y sin afectación a la operación.'
            ],
            [
                'name' => 'Media',
                'slug' => 'medium',
                'description' => 'Incidente con impacto moderado, requiere atención en el mismo día.'
            ],
            [
                'name' => 'Alta',
                'slug' => 'high',
                'description' => 'Incidente con impacto alto, requiere respuesta inmediata.'
            ],
        ];

        foreach ($criticals as $key => $value) {
            Critical::updateOrCreate(
                ['slug'=> $value['slug']],
                [
                    'name'=> $value['name'],
                    'description'=> $value['description'],
                ]
            );
        }
    }
}
