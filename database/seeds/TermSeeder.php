<?php

use App\Models\Term;
use Illuminate\Database\Seeder;

class TermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            ['7.1 nov 3 hotfix', '2016-11-03', null],
        ] as $data) {
            $term = Term::firstOrCreate([
                'name' => $data[0],
                'start_date' => $data[1],
            ]);
            $term->end_date = $data[2];
            $term->save();
        }
    }
}
