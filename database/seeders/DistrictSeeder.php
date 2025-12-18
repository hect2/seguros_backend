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
            ['code' => 'DINOC',     'name' => 'Distrito Norte Central',     'status' => 1,],
            ['code' => 'DICE',      'name' => 'Distrito Central',           'status' => 1,],
            ['code' => 'DINOR',     'name' => 'Distrito Noreste',           'status' => 1,],
            ['code' => 'DISO',      'name' => 'Distrito Suroccidente',      'status' => 1,],
            ['code' => 'DISO_SUR',  'name' => 'Distrito Sur',               'status' => 0,],
            ['code' => 'DIOR',      'name' => 'Distrito Oriente',           'status' => 1,],
        ];

        foreach ($districts as $data) {
            District::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
