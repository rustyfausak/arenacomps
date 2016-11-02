<?php

use App\Models\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            [0, 'Male'],
            [1, 'Female'],
        ] as $data) {
            Gender::firstOrCreate([
                'id' => $data[0],
                'name' => $data[1],
            ]);
        }
    }
}
