<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        Business::create([
            'name' => 'Banrural',
            'direction' => fake()->city(),
            'phone' => fake()->phoneNumber(),
        ]);
    }
}
