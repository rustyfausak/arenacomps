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
            '2v2',
            '3v3',
            '5v5',
            'rbg'
        ] as $name) {
            Bracket::firstOrCreate(['name' => $name]);
        }
    }
}
