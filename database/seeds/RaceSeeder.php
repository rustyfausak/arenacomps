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
            [1, 'Human', 1],
            [2, 'Orc', 2],
            [3, 'Dwarf', 1],
            [4, 'Night Elf',1],
            [5, 'Undead', 2],
            [6, 'Tauren', 2],
            [7, 'Gnome', 1],
            [8, 'Troll', 2],
            [9, 'Goblin', 2],
            [10, 'Blood Elf',2],
            [11, 'Draenei', 1],
            [22, 'Worgen', 1],
            [25, 'Pandaren', 1],
            [26, 'Pandaren', 2],
        ] as $data) {
            Race::firstOrCreate([
                'id' => $data[0],
                'name' => $data[1],
                'faction_id' => $data[2],
            ]);
        }
    }
}
