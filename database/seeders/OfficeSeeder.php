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
        $offices = [
            // DINOC
            [
                'district_id' => District::where("code", "DINOC")->first()->id,
                'user_id' => 3,
                'code' => 'OF001',
                'name' => 'OTR COBÁN',
                'direction' => 'Cobán, Alta Verapaz',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-20',
            ],
            [
                'district_id' => District::where("code", "DINOC")->first()->id,
                'user_id' => null,  // Sin asignar
                'code' => 'OF002',
                'name' => 'OTR SALAMÁ',
                'direction' => 'Salamá',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-19',
            ],
            [
                'district_id' => District::where("code", "DINOC")->first()->id,
                'user_id' => null,  // Sin asignar
                'code' => 'OF003',
                'name' => 'OTR QUICHÉ',
                'direction' => 'Quiché',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-18',
            ],

            // DICE
            [
                'district_id' => District::where("code", "DICE")->first()->id,
                'user_id' => 4,     // Reemplaza con el ID real del usuario Alangumer Gonzalo Eduardo Ángel López
                'code' => 'OF004',
                'name' => 'AREA NORTE',
                'direction' => 'Zona 1, Ciudad de Guatemala',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-20',
            ],
            [
                'district_id' => District::where("code", "DICE")->first()->id,
                'user_id' => null,  // Sin asignar
                'code' => 'OF005',
                'name' => 'AREA SUR',
                'direction' => 'Sur, Ciudad de Guatemala',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-19',
            ],
            [
                'district_id' => District::where("code", "DICE")->first()->id,
                'user_id' => null,  // Sin asignar
                'code' => 'OF006',
                'name' => 'OTR ESCUINTLA',
                'direction' => 'Escuintla',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-18',
            ],
            [
                'district_id' => District::where("code", "DICE")->first()->id,
                'user_id' => null,  // Sin asignar
                'code' => 'OF007',
                'name' => 'OTR SACATEPÉQUEZ',
                'direction' => 'Sacatepéquez',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-17',
            ],

            // DINOR
            [
                'district_id' => District::where("code", "DINOR")->first()->id,
                'user_id' => null,  // Sin asignar
                'code' => 'OF008',
                'name' => 'OTR PETÉN',
                'direction' => 'Petén',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-20',
            ],
            [
                'district_id' => District::where("code", "DINOR")->first()->id,
                'user_id' => null,  // Sin asignar
                'code' => 'OF009',
                'name' => 'OTR IZABAL',
                'direction' => 'Izabal',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-19',
            ],
            [
                'district_id' => District::where("code", "DINOR")->first()->id,
                'user_id' => null,  // Sin asignar
                'code' => 'OF010',
                'name' => 'OTR ZACAPA',
                'direction' => 'Zacapa',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-18',
            ],
            [
                'district_id' => District::where("code", "DINOR")->first()->id,
                'user_id' => null,  // Sin asignar
                'code' => 'OF011',
                'name' => 'OTR CHIQUIMULA',
                'direction' => 'Chiquimula',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 1, // Activo
                'created_at' => '2025-10-17',
            ],

            // DISO_SUR
            [
                'district_id' => District::where("code", "DISO_SUR")->first()->id,
                'user_id' => null,  // Sin asignar
                'code' => 'OF012',
                'name' => 'OTR RETALHULEU',
                'direction' => 'Retalhuleu',
                'phone' => $faker->phoneNumber,
                'observations' => $faker->sentence(),
                'status' => 0, // Inactivo
                'created_at' => '2025-10-16',
            ],
        ];

        // District::where("code", "DISO_SUR")->first()->id,


        foreach ($offices as $data) {
            Office::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
