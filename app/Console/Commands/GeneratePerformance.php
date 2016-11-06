<?php

namespace App\Console\Commands;

use DB;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

use App\Models\Bracket;
use App\Models\Comp;
use App\Models\Faction;
use App\Models\Gender;
use App\Models\Leaderboard;
use App\Models\Player;
use App\Models\Race;
use App\Models\Realm;
use App\Models\Region;
use App\Models\Role;
use App\Models\Season;
use App\Models\Snapshot;
use App\Models\Spec;
use App\Models\Stat;
use App\Models\Team;
use App\Models\Term;

class GeneratePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'performance:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the performance data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $comps = Comp::all();
            $season = Season::getDefault();
            $term = Term::getDefault();
            $regions = Region::all();
            foreach ($comps as $comp) {
                $comp->getPerformance($season, null, null, $term);
                $comp->getPerformance($season, null, null, null);
                foreach ($regions as $region) {
                    $comp->getPerformance($season, $region, null, $term);
                    $comp->getPerformance($season, $region, null, null);
                }
            }
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }
}
