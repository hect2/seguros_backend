<?php

namespace Database\Seeders;

use App\Models\Tracking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trackings = [
            [
                'employee_id' => 1,
                'name' => 'Onboarding Process',
                'responsible' => 'HR Department',
                'approval_date' => '2024-11-03 10:30:00',
                'status' => 1,
                'description' => 'Employee completed onboarding steps.',
            ],
            [
                'employee_id' => 1,
                'name' => 'Promotion Review',
                'responsible' => 'Management',
                'approval_date' => null,
                'status' => 1,
                'description' => 'Pending approval for promotion.',
            ],
            [
                'employee_id' => 2,
                'name' => 'Annual Evaluation',
                'responsible' => 'Supervisor Team',
                'approval_date' => '2024-10-15 15:00:00',
                'status' => 1,
                'description' => 'Performance rated as excellent.',
            ],
        ];

        Tracking::insert($trackings);
    }
}
