<?php

use App\Models\Bracket;
use Illuminate\Database\Seeder;

class BracketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            ['2v2', 2],
            ['3v3', 3],
        ] as $data) {
            Bracket::firstOrCreate([
                'name' => $data[0],
                'size' => $data[1]
            ]);
        }
    }
}
