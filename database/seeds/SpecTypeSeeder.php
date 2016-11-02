<?php

use App\Models\SpecType;
use Illuminate\Database\Seeder;

class SpecTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            [1, 'Tank'],
            [2, 'Healer'],
            [3, 'DPS'],
        ] as $data) {
            SpecType::firstOrCreate([
                'id' => $data[0],
                'name' => $data[1],
            ]);
        }
    }
}
