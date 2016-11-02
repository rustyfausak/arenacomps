<?php

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            [1, 'Warrior'],
            [2, 'Paladin'],
            [3, 'Hunter'],
            [4, 'Rogue'],
            [5, 'Priest'],
            [6, 'Death Knight'],
            [7, 'Shaman'],
            [8, 'Mage'],
            [9, 'Warlock'],
            [10, 'Monk'],
            [11, 'Druid'],
            [12, 'Demon Hunter'],
        ] as $data) {
            Role::firstOrCreate([
                'id' => $data[0],
                'name' => $data[1],
            ]);
        }
    }
}
