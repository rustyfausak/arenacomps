<?php

use App\Models\Race;
use Illuminate\Database\Seeder;

class RaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            [1, 'Human', 0],
            [2, 'Orc', 1],
            [3, 'Dwarf', 0],
            [4, 'Night Elf', 0],
            [5, 'Undead', 1],
            [6, 'Tauren', 1],
            [7, 'Gnome', 0],
            [8, 'Troll', 1],
            [9, 'Goblin', 1],
            [10, 'Blood Elf', 1],
            [11, 'Draenei', 0],
            [22, 'Worgen', 0],
            [25, 'Pandaren', 1],
            [26, 'Pandaren', 0],
        ] as $data) {
            Race::firstOrCreate([
                'id' => $data[0],
                'name' => $data[1],
                'faction_id' => $data[2],
            ]);
        }
    }
}
