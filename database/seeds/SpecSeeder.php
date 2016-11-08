<?php

use App\Models\Spec;
use Illuminate\Database\Seeder;

class SpecSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            [62, 'Arcane', 8, 3],
            [63, 'Fire', 8, 3],
            [64, 'Frost', 8, 3],

            [65, 'Holy', 2, 2],
            [66, 'Protection', 2, 1],
            [70, 'Retribution', 2, 3],

            [71, 'Arms', 1, 3],
            [72, 'Fury', 1, 3],
            [73, 'Protection', 1, 1],

            [102, 'Balance', 11, 3],
            [103, 'Feral', 11, 3],
            [104, 'Guardian', 11, 1],
            [105, 'Restoration', 11, 2],

            [250, 'Blood', 6, 1],
            [251, 'Frost', 6, 3],
            [252, 'Unholy', 6, 3],

            [253, 'Beast Mastery',3, 3],
            [254, 'Marksmanship', 3, 3],
            [255, 'Survival', 3, 3],

            [256, 'Discipline', 5, 2],
            [257, 'Holy', 5, 2],
            [258, 'Shadow', 5, 3],

            [259, 'Assassination', 4, 3],
            [260, 'Outlaw', 4, 3],
            [261, 'Subtlety', 4, 3],

            [262, 'Elemental', 7, 3],
            [263, 'Enhancement', 7, 3],
            [264, 'Restoration', 7, 2],

            [265, 'Affliction', 9, 3],
            [266, 'Demonology', 9, 3],
            [267, 'Destruction', 9, 3],

            [268, 'Brewmaster', 10, 1],
            [269, 'Windwalker', 10, 3],
            [270, 'Mistweaver', 10, 2],

            [577, 'Havoc', 12, 3],
            [581, 'Vengeance', 12, 1],
        ] as $data) {
            Spec::firstOrCreate([
                'id' => $data[0],
                'name' => $data[1],
                'role_id' => $data[2],
                'spec_type_id' => $data[3]
            ]);
        }
    }
}
