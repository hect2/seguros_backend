<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(40, 100) as $i) {
            Office::create([
                'district_id' => District::inRandomOrder()->first()->id,
                'user_id' => User::inRandomOrder()->first()->id,
                'code' => 'OF' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => $faker->company,
                'direction' => $faker->address,
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => $faker->randomElement([0, 1]),
            ]);
        }
    }
}
