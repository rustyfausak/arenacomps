<?php

use App\Models\Season;
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
            ['7.1 nov 3 hotfix', '2016-11-03', '2016-11-14'],
            ['7.1 nov 15 hotfix', '2016-11-15', null],
        ] as $data) {
            $date = $data[1];
            $season = Season::where('start_date', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->where('end_date', '>=', $date)
                        ->orWhereNull('end_date');
                })
                ->orderBy('start_date', 'DESC')
                ->first();
            if (!$season) {
                throw new \Exception("No season found for term: " . implode(',', $data));
            }
            $term = Term::firstOrCreate([
                'season_id' => $season->id,
                'name' => $data[0],
                'start_date' => $data[1],
            ]);
            $term->end_date = $data[2];
            $term->save();
        }
    }
}
