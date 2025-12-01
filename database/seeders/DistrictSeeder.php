<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\District;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $districts = [
            ['code' => 'D001', 'name' => 'Central District', 'description' => 'Main administrative zone', 'status' => 1],
            ['code' => 'D002', 'name' => 'North District', 'description' => 'Northern operations area', 'status' => 1],
            ['code' => 'D003', 'name' => 'South District', 'description' => 'Southern zone', 'status' => 1],
        ];

        for ($i = 4; $i <= 50; $i++) {
            $districts[] = [
                'code' => 'D' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => ucfirst($faker->unique()->city) . ' District',
                'description' => $faker->sentence(6),
                'status' => $faker->randomElement([0, 1]),
                'business_id' => Business::inRandomOrder()->first()->id
            ];
        }

        foreach ($districts as $data) {
            District::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
