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
use App\Models\Snapshot;
use App\Models\Spec;
use App\Models\Stat;
use App\Models\Team;

class ClearComps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'comps:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear comp data.';

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
     * Specify the command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('snapshots')
            ->update([
                'team_id' => null,
                'comp_id' => null
            ]);
        DB::table('teams')->truncate();
        DB::table('comps')->truncate();
        DB::table('performance')->truncate();
    }
}
