<?php

namespace Database\Seeders;

use App\Models\Critical;
use App\Models\Incident;
use App\Models\IncidentStatus;
use App\Models\Office;
use App\Models\Type;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 10) as $i) {

            $isfile = fake()->boolean();

            if ($isfile) {
                $files = [
                    [
                        'name' => fake()->lexify('captura_????.png'),
                        'path' => '/storage/incidents/' . fake()->numberBetween(1, 50) . '/' . fake()->lexify('captura_????.png'),
                        'mime_type' => 'image/png',
                        'size_kb' => fake()->numberBetween(50, 500),
                        'uploaded_at' => fake()->iso8601(),
                    ]
                ];
            } else {
                $files = [];
            }

            $isUserAssigment = fake()->boolean();
            if ($isUserAssigment) {
                $user_assigned = User::inRandomOrder()->first()->id;
            }
            else {
                $user_assigned = null;
            }

            Incident::create([
                'title' => "Incidente #$i",
                'type_id' => Type::inRandomOrder()->first()->id,
                'office_id' => Office::inRandomOrder()->first()->id,
                'criticity_id' => Critical::inRandomOrder()->first()->id,
                'description' => fake()->paragraph(),
                'files' => json_encode($files),
                'status_id' => IncidentStatus::inRandomOrder()->first()->id,
                'user_reported' => User::inRandomOrder()->first()->id,
                'user_assigned' => $user_assigned,
            ]);
        }
    }
}
