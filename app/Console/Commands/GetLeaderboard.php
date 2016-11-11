<?php

namespace App\Console\Commands;

use DB;
use App\BattleNetApi;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

use App\Models\Bracket;
use App\Models\Faction;
use App\Models\Gender;
use App\Models\Group;
use App\Models\Leaderboard;
use App\Models\Player;
use App\Models\Race;
use App\Models\Realm;
use App\Models\Region;
use App\Models\Rep;
use App\Models\Role;
use App\Models\Season;
use App\Models\Snapshot;
use App\Models\Spec;
use App\Models\Stat;
use App\Models\Term;

class GetLeaderboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'leaderboard:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the PvP leaderboard data.';

    /**
     * The BattleNetApi service.
     *
     * @var BattleNetApi
     */
    protected $battle_net_api;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BattleNetApi $battle_net_api)
    {
        parent::__construct();

        $this->battle_net_api = $battle_net_api;
    }

    /**
     * Specify the command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['region', 'r', InputOption::VALUE_OPTIONAL, 'The region.', 'us'],
            ['bracket', 'b', InputOption::VALUE_OPTIONAL, 'The bracket.', '3v3'],
            ['locale', 'l', InputOption::VALUE_OPTIONAL, 'The locale for the response. Defaults to english.', null],
            ['save', 's', InputOption::VALUE_OPTIONAL, 'Save the response data to a file path.', null],
            ['file', 'f', InputOption::VALUE_OPTIONAL, 'Use a file instead of requesting data.', null],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $season = Season::getActive();
            if (!$season) {
                throw new \Exception("No active season found.");
            }
            $term = Term::getActive();
            if (!$term) {
                throw new \Exception("No active term found.");
            }
            if ($this->option('file')) {
                $leaderboard = $this->parseFile($this->option('file'));
            }
            else {
                $region = Region::where('name', 'LIKE', $this->option('region'))->first();
                if (!$region) {
                    throw new \Exception("Invalid region '{$this->option('region')}'.");
                }
                $bracket = Bracket::where('name', 'LIKE', $this->option('bracket'))->first();
                if (!$bracket) {
                    throw new \Exception("Invalid bracket '{$this->option('bracket')}'.");
                }
                $data = $this->battle_net_api->getPvpLeaderboard(
                    $region->name,
                    $bracket->name,
                    $this->option('locale')
                );
                if (!$data) {
                    throw new \Exception("No response from server.");
                }
                $data = json_decode($data, true);
                if (!$data) {
                    throw new \Exception("Could not parse JSON data.");
                }
                $data['region'] = $region->name;
                $data['bracket'] = $bracket->name;
                $data['locale'] = $this->option('locale');
                $data['created_at'] = date("Y-m-d H:i:s");
                if ($this->option('save')) {
                    file_put_contents($this->option('save'), json_encode($data, JSON_PRETTY_PRINT));
                }
                $leaderboard = $this->parse($data);
            }
            $this->generateRep($leaderboard);
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }

    /**
     * Generate representation data for this leaderboard.
     *
     * @param Leaderboard $leaderboard
     */
    public function generateRep(Leaderboard $leaderboard)
    {
        $datas = [
            'role' => [],
            'spec' => [],
            'race' => [],
        ];

        $datas['role'] = DB::table('stats AS s')
            ->select([
                'p.role_id',
                DB::raw('COUNT(*) AS num'),
            ])
            ->leftJoin('players AS p', 's.player_id', '=', 'p.id')
            ->leftJoin('roles AS r', 'p.role_id', '=', 'r.id')
            ->where('s.leaderboard_id', '=', $leaderboard->id)
            ->groupBy('p.role_id')
            ->orderBy('num', 'DESC')
            ->get()
            ->toArray();

        $datas['spec'] = DB::table('stats AS s')
            ->select([
                'p.role_id',
                'p.spec_id',
                DB::raw('COUNT(*) AS num'),
            ])
            ->leftJoin('players AS p', 's.player_id', '=', 'p.id')
            ->leftJoin('specs AS sp', 'p.spec_id', '=', 'sp.id')
            ->leftJoin('roles AS r', 'sp.role_id', '=', 'r.id')
            ->where('s.leaderboard_id', '=', $leaderboard->id)
            ->groupBy('p.spec_id')
            ->orderBy('num', 'DESC')
            ->get()
            ->toArray();

        $datas['race'] = DB::table('stats AS s')
            ->select([
                'p.race_id',
                DB::raw('COUNT(*) AS num'),
            ])
            ->leftJoin('players AS p', 's.player_id', '=', 'p.id')
            ->leftJoin('races AS r', 'p.race_id', '=', 'r.id')
            ->where('s.leaderboard_id', '=', $leaderboard->id)
            ->groupBy('p.race_id')
            ->orderBy('num', 'DESC')
            ->get()
            ->toArray();

        $datas['role_race'] = DB::table('stats AS s')
            ->select([
                'p.role_id',
                'p.race_id',
                DB::raw('COUNT(*) AS num'),
            ])
            ->leftJoin('players AS p', 's.player_id', '=', 'p.id')
            ->leftJoin('races AS r', 'p.race_id', '=', 'r.id')
            ->where('s.leaderboard_id', '=', $leaderboard->id)
            ->groupBy('p.role_id', 'p.race_id')
            ->orderBy('num', 'DESC')
            ->get()
            ->toArray();

        $datas['spec_race'] = DB::table('stats AS s')
            ->select([
                'p.role_id',
                'p.spec_id',
                'p.race_id',
                DB::raw('COUNT(*) AS num'),
            ])
            ->leftJoin('players AS p', 's.player_id', '=', 'p.id')
            ->leftJoin('races AS r', 'p.race_id', '=', 'r.id')
            ->where('s.leaderboard_id', '=', $leaderboard->id)
            ->groupBy('p.spec_id', 'p.race_id')
            ->orderBy('num', 'DESC')
            ->get()
            ->toArray();

        Rep::where('leaderboard_id', '=', $leaderboard->id)->delete();

        foreach ($datas as $k => $data) {
            foreach ($data as $obj) {
                $params = [
                    'leaderboard_id' => $leaderboard->id,
                    'num' => $obj->num,
                ];
                if (isset($obj->role_id)) {
                    $params['role_id'] = $obj->role_id;
                }
                if (isset($obj->spec_id)) {
                    $params['spec_id'] = $obj->spec_id;
                }
                if (isset($obj->race_id)) {
                    $params['race_id'] = $obj->race_id;
                }
                Rep::create($params);
            }
        }
    }

    /**
     * Reads data from a file then parses it.
     *
     * @param string $file
     * @return Leaderboard
     * @throws Exception
     */
    public function parseFile($file)
    {
        $data = file_get_contents($file);
        if (!$data) {
            throw new \Exception("Could not read data from file.");
        }
        $data = json_decode($data, true);
        if (!$data) {
            throw new \Exception("Could not parse JSON data.");
        }
        return $this->parse($data);
    }

    /**
     * Parse leaderboard data.
     *
     * @param array $data
     * @return Leaderboard
     */
    public function parse($data)
    {
        if (!$data || !is_array($data)) {
            $this->error("Invalid data.");
            return;
        }
        foreach ([
            'rows',
            'region',
            'bracket',
            'locale',
            'created_at',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                $this->error("Invalid data: Missing '{$key}'.");
                return;
            }
        }
        $region = Region::where('name', 'LIKE', $data['region'])->first();
        if (!$region) {
            throw new \Exception("Invalid region '{$data['region']}'.");
        }
        $bracket = Bracket::where('name', 'LIKE', $data['bracket'])->first();
        if (!$bracket) {
            throw new \Exception("Invalid bracket '{$data['bracket']}'.");
        }
        $ts = strtotime($data['created_at']);
        if (!$ts) {
            throw new \Exception("Invalid created_at '{$data['created_at']}'.");
        }
        $created_at = date("Y-m-d H:i:s", $ts);

        $races = Race::all()->getDictionary();
        $roles = Role::all()->getDictionary();
        $specs = Spec::all()->getDictionary();
        $factions = Faction::all()->getDictionary();
        $genders = Gender::all()->getDictionary();

        foreach ($data['rows'] as $n => $row) {
            foreach ([
                'ranking',
                'rating',
                'name',
                'realmId',
                'realmName',
                'realmSlug',
                'raceId',
                'classId',
                'specId',
                'factionId',
                'genderId',
                'seasonWins',
                'seasonLosses',
                'weeklyWins',
                'weeklyLosses',
            ] as $key) {
                if (!array_key_exists($key, $row)) {
                    throw new \Exception("Invalid row #{$n}: Missing key '{$key}'.");
                }
            }
            if (!in_array($row['raceId'], array_keys($races))) {
                throw new \Exception("Invalid raceId on row #{$n}: '{$row['raceId']}'.");
            }
            if (!in_array($row['classId'], array_keys($roles))) {
                throw new \Exception("Invalid classId on row #{$n}: '{$row['classId']}'.");
            }
            if (!in_array($row['specId'], array_keys($specs))) {
                throw new \Exception("Invalid specId on row #{$n}: '{$row['specId']}'.");
            }
            if (!in_array($row['factionId'], array_keys($factions))) {
                throw new \Exception("Invalid factionId on row #{$n}: '{$row['factionId']}'.");
            }
            if (!in_array($row['genderId'], array_keys($genders))) {
                throw new \Exception("Invalid genderId on row #{$n}: '{$row['genderId']}'.");
            }
        }

        $season = Season::getActive();
        $term = Term::getActive();
        $leaderboard = Leaderboard::create([
            'region_id' => $region->id,
            'bracket_id' => $bracket->id,
            'season_id' => $season->id,
            'term_id' => $term->id,
        ]);

        $update_stat_ids = [];

        foreach ($data['rows'] as $n => $row) {
            $realm = Realm::where('slug', '=', $row['realmSlug'])
                ->where('region_id', '=', $region->id)
                ->first();
            if (!$realm) {
                $realm = Realm::create([
                    'region_id' => $region->id,
                    'name' => $row['realmName'],
                    'slug' => $row['realmSlug'],
                ]);
            }
            $uid = Player::getUid($row['name']);
            $player = Player::where('uid', '=', $uid)
                ->where('realm_id', '=', $realm->id)
                ->where('faction_id', '=', $row['factionId'])
                ->first();
            if ($player) {
                $player->race_id = $row['raceId'];
                $player->role_id = $row['classId'];
                $player->spec_id = $row['specId'];
                $player->gender_id = $row['genderId'];
                $player->save();
            }
            else {
                $player = Player::create([
                    'uid' => $uid,
                    'name' => $row['name'],
                    'realm_id' => $realm->id,
                    'faction_id' => $row['factionId'],
                    'race_id' => $row['raceId'],
                    'role_id' => $row['classId'],
                    'spec_id' => $row['specId'],
                    'gender_id' => $row['genderId'],
                ]);
            }
            $stat = Stat::where('player_id', '=', $player->id)
                ->where('bracket_id', '=', $bracket->id)
                ->first();
            if ($stat) {
                if ($stat->season_wins != $row['seasonWins'] || $stat->season_losses != $row['seasonLosses']) {
                    $group = Group::firstOrCreate([
                        'leaderboard_id' => $leaderboard->id,
                        'faction_id' => $row['factionId'],
                        'wins' => max(0, $row['seasonWins'] - $stat->season_wins),
                        'losses' => max(0, $row['seasonLosses'] - $stat->season_losses),
                    ]);
                    Snapshot::create([
                        'player_id' => $player->id,
                        'group_id' => $group->id,
                        'spec_id' => $row['specId'],
                        'rating' => $row['rating'],
                    ]);
                }
                $update_stat_ids[] = $stat->id;
                $stat->ranking = $row['ranking'];
                $stat->rating = $row['rating'];
                $stat->season_wins = $row['seasonWins'];
                $stat->season_losses = $row['seasonLosses'];
                $stat->weekly_wins = $row['weeklyWins'];
                $stat->weekly_losses = $row['weeklyLosses'];
                $stat->save();
            }
            else {
                $stat = Stat::create([
                    'player_id' => $player->id,
                    'bracket_id' => $bracket->id,
                    'leaderboard_id' => $leaderboard->id,
                    'ranking' => $row['ranking'],
                    'rating' => $row['rating'],
                    'season_wins' => $row['seasonWins'],
                    'season_losses' => $row['seasonLosses'],
                    'weekly_wins' => $row['weeklyWins'],
                    'weekly_losses' => $row['weeklyLosses'],
                ]);
            }
        }

        Stat::whereIn('id', $update_stat_ids)
            ->update(['leaderboard_id' => $leaderboard->id]);

        $leaderboard->completed_at = date("Y-m-d H:i:s");
        $leaderboard->save();

        return $leaderboard;
    }
}
