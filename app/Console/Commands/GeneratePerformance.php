<?php

namespace App\Console\Commands;

use DB;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

use App\OptionsManager;
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
                $bracket = $comp->getBracket();
                print "comp #{$comp->id}\n";
                $om = new OptionsManager($season, $bracket, null, $term);
                $comp->getPerformance($om);
                $om = new OptionsManager($season, $bracket, null, null);
                $comp->getPerformance($om);
                foreach ($regions as $region) {
                    $om = new OptionsManager($season, $bracket, $region, null);
                    $comp->getPerformance($om);
                    $om = new OptionsManager($season, $bracket, $region, $term);
                    $comp->getPerformance($om);
                }
            }
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }
}
