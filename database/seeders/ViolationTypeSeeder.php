<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ViolationType;

class ViolationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'No ID', 'description' => 'Student entered without a valid ID.'],
            ['name' => 'Eating/Drinking', 'description' => 'Student brought food or drinks inside the library.'],
            ['name' => 'Noise', 'description' => 'Student caused a disturbance or was too loud.'],
            ['name' => 'Vandalism', 'description' => 'Student damaged library property or books.'],
            ['name' => 'Overdue Books', 'description' => 'Student failed to return borrowed books on time.'],
        ];

        foreach ($types as $type) {
            ViolationType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
