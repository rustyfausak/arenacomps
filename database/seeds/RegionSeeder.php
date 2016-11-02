<?php

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (['us', 'eu'] as $name) {
            Region::firstOrCreate(['name' => $name]);
        }
    }
}
