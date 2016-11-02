<?php

use App\Models\Faction;
use Illuminate\Database\Seeder;

class FactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            [0, 'Alliance'],
            [1, 'Horde'],
        ] as $data) {
            Faction::firstOrCreate([
                'id' => $data[0],
                'name' => $data[1],
            ]);
        }
    }
}
