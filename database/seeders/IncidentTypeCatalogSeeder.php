<?php

namespace Database\Seeders;

use App\Models\IncidentTypeCatalog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IncidentTypeCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['Importantes'],
            ['Negativas'],
            ['Supervisiones'],
            ['Permisos'],
            ['Faltando'],
            ['Servicios Especiales'],
            ['Vacaciones'],
            ['Rutinas'],
        ];

        foreach ($types as [$name]) {
            IncidentTypeCatalog::firstOrCreate([
                'slug' => Str::slug($name)
            ], [
                'name' => $name,
                'active' => true
            ]);
        }
    }
}