<?php

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            ['legion season 1', '2016-08-30', null],
        ] as $data) {
            $term = Season::firstOrCreate([
                'name' => $data[0],
                'start_date' => $data[1],
            ]);
            $term->end_date = $data[2];
            $term->save();
        }
    }
}
