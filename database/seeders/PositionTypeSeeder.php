<?php

namespace Database\Seeders;

use App\Models\PositionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $position_types = [
            [
                'name' => 'Administrator',
                'category' => 'Administrative',
                'slug' => 'administrator',
                'description' => 'Responsible for office administrative tasks'
            ],
            [
                'name' => 'Operator',
                'category' => 'Operational',
                'slug' => 'operator',
                'description' => 'Performs field or operational tasks'
            ],
            [
                'name' => 'Supervisor',
                'category' => 'Administrative',
                'slug' => 'supervisor',
                'description' => 'Supervises and coordinates staff activities'
            ],
        ];

        foreach ($position_types as $key => $value) {
            PositionType::updateOrCreate(
                ['slug' => $value['slug']],
                [
                    'name' => $value['name'],
                    'category' => $value['category'],
                    'description' => $value['description'],
                ]
            );
        }
    }
}
