<?php

namespace App\Console\Commands;

use DB;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

use App\Models\Bracket;
use App\Models\Comp;
use App\Models\Faction;
use App\Models\Gender;
use App\Models\Group;
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

class GenerateComps_v2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'comps:generate-v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the comp data.';

    const SESSION_LIMIT_MINUTES = 19;
    const MIN_SESSION_SNAPSHOTS = 4;
    const MIN_SESSION_GAMES = 4;
    const ALLOWED_GAME_DIFF = 1;
    const MIN_TEAMMATE_SESSION_COUNT = 2;
    protected $debug;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->debug = false;
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
        try {
            foreach (Bracket::all() as $bracket) {
                print "Bracket {$bracket->name}\n";
                $q = DB::table('snapshots AS s')
                    ->leftJoin('groups AS g', 'g.id', '=', 's.group_id')
                    ->leftJoin('leaderboards AS l', 'l.id', '=', 'g.leaderboard_id')
                    ->where('l.bracket_id', '=', $bracket->id)
                    ->whereNull('s.team_id')
                    ->whereNull('s.comp_id')
                    ->groupBy('s.player_id')
                    ->orderBy('s.player_id', 'ASC');
                foreach ($q->pluck('s.player_id') as $player_id) {
                    $this->processPlayerById($bracket, $player_id);
                }
            }
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }

    public function processPlayerById($bracket, $player_id)
    {
        print "\tplayer #{$player_id}\n";
        $q = DB::table('snapshots AS s')
            ->select([
                'l.created_at',
                'l.id AS leaderboard_id',
                'g.wins',
                'g.losses',
                'g.faction_id',
                's.id AS snapshot_id',
                's.rating',
                's.spec_id',
            ])
            ->leftJoin('groups AS g', 'g.id', '=', 's.group_id')
            ->leftJoin('leaderboards AS l', 'l.id', '=', 'g.leaderboard_id')
            ->where('l.bracket_id', '=', $bracket->id)
            ->where('s.player_id', '=', $player_id)
            ->whereNull('s.team_id')
            ->whereNull('s.comp_id')
            ->orderBy('l.id', 'ASC');
        $sessions = [];
        $cur_session = null;
        $last_ts = null;
        $last_spec_id = null;
        foreach ($q->get() as $row) {
            $cur_ts = strtotime($row->created_at);
            if (!$last_ts) {
                $cur_session = new ArenaSession($row);
            }
            else {
                if ($row->spec_id != $last_spec_id || $cur_ts - $last_ts > 60 * self::SESSION_LIMIT_MINUTES) {
                    $sessions[] = $cur_session;
                    $cur_session = new ArenaSession($row);
                }
                else {
                    $cur_session->extend($row);
                }
            }
            $last_ts = $cur_ts;
            $last_spec_id = $row->spec_id;
        }
        if ($cur_session) {
            $sessions[] = $cur_session;
        }
        foreach ($sessions as $session) {
            if ($session->num < self::MIN_SESSION_SNAPSHOTS) {
                continue;
            }
            if ($session->wins + $session->losses < self::MIN_SESSION_GAMES) {
                continue;
            }
            $this->processArenaSession($session, $bracket, $player_id);
        }

        // Count up the number of teams each player was put on a team together
        $by_player_id = [];
        foreach ($sessions as $session) {
            if (!$session->result) {
                continue;
            }
            foreach ($session->result['player_ids'] as $team_player_id) {
                if ($team_player_id == $player_id) {
                    continue;
                }
                if (!array_key_exists($team_player_id, $by_player_id)) {
                    $by_player_id[$team_player_id] = 0;
                }
                $by_player_id[$team_player_id]++;
            }
        }

        foreach ($sessions as $session) {
            if (!$session->result) {
                continue;
            }
            $all = true;
            foreach ($session->result['player_ids'] as $team_player_id) {
                if ($team_player_id == $player_id) {
                    continue;
                }
                if ($by_player_id[$team_player_id] < self::MIN_TEAMMATE_SESSION_COUNT) {
                    $all = false;
                    break;
                }
            }
            if ($all) {
                $team = Team::getOrBuild($session->result['player_ids']);
                $comp = Comp::getOrBuild($session->result['spec_ids']);
                DB::table('snapshots AS s')
                    ->leftJoin('groups AS g', 'g.id', '=', 's.group_id')
                    ->leftJoin('leaderboards AS l', 'l.id', '=', 'g.leaderboard_id')
                    ->whereIn('l.id', $session->leaderboard_ids)
                    ->whereIn('s.player_id', $session->result['player_ids'])
                    ->update([
                        's.team_id' => $team->id,
                        's.comp_id' => $comp->id
                    ]);
                print "\t\t\tsession {$session->start_leaderboard_id} :: team {$team->id} comp {$comp->id} " . implode(',', $session->result['player_ids']) . "\n";
            }
        }
    }

    public function processArenaSession($session, $bracket, $player_id)
    {
        print "\t\tsession {$session->start_leaderboard_id} - {$session->end_leaderboard_id}\n";
        $session->leaderboard_ids = $session->getLeaderboardIds();
        if ($this->debug) {
            print_r($session);
            print_r($leaderboard_ids);
        }
        $q = DB::table('snapshots AS s')
            ->select([
                's.player_id',
                DB::raw('SUM(g.wins) AS wins'),
                DB::raw('SUM(g.losses) AS losses'),
                DB::raw('AVG(s.rating) AS rating'),
                DB::raw('GROUP_CONCAT(s.spec_id) AS spec_ids'),
            ])
            ->leftJoin('groups AS g', 'g.id', '=', 's.group_id')
            ->leftJoin('leaderboards AS l', 'l.id', '=', 'g.leaderboard_id')
            ->whereIn('l.id', $session->leaderboard_ids)
            ->where('s.player_id', '!=', $player_id)
            ->where('g.faction_id', '=', $session->faction_id)
            ->groupBy('s.player_id');
        $related = [];
        foreach ($q->get() as $row) {
            if (abs($row->wins + $row->losses - ($session->wins + $session->losses)) > self::ALLOWED_GAME_DIFF) {
                continue;
            }
            if (abs($row->wins - $session->wins) > self::ALLOWED_GAME_DIFF) {
                continue;
            }
            if (abs($row->losses - $session->losses) > self::ALLOWED_GAME_DIFF) {
                continue;
            }
            $row->spec_ids = array_count_values(explode(',', $row->spec_ids));
            $related[] = $row;
        }
        if (sizeof($related) < $bracket->size - 1) {
            return;
        }
        $sort = [];
        foreach ($related as $k => $row) {
            $game_diff = abs($row->wins + $row->losses - ($session->wins + $session->losses));
            $sort[$k] = ($game_diff + 1) * abs($row->rating - $session->rating);
        }
        array_multisort($sort, SORT_ASC, $related);
        $related = array_slice($related, 0, $bracket->size - 1);
        $player_ids = [$player_id];
        arsort($session->spec_ids);
        $spec_ids = [key($session->spec_ids)];
        foreach ($related as $row) {
            $player_ids[] = $row->player_id;
            arsort($row->spec_ids);
            $spec_ids[] = key($row->spec_ids);
        }
        if (sizeof($player_ids) != $bracket->size || sizeof($spec_ids) != $bracket->size) {
            return;
        }
        $session->result = [
            'player_ids' => $player_ids,
            'spec_ids' => $spec_ids,
        ];
    }
}

class ArenaSession
{
    public function __construct($row)
    {
        $this->start_leaderboard_id = $row->leaderboard_id;
        $this->end_leaderboard_id = $row->leaderboard_id;
        $this->wins = $row->wins;
        $this->losses = $row->losses;
        $this->faction_id = $row->faction_id;
        $this->num = 1;
        $this->rating = $row->rating;
        $this->spec_ids[$row->spec_id] = 1;
        $this->result = null;
        $this->leaderboard_ids = [];
    }

    public function extend($row)
    {
        $this->start_leaderboard_id = min($row->leaderboard_id, $this->start_leaderboard_id);
        $this->end_leaderboard_id = max($row->leaderboard_id, $this->end_leaderboard_id);
        $this->wins += $row->wins;
        $this->losses += $row->losses;
        $this->rating = ($this->rating * $this->num + $row->rating) / ($this->num + 1);
        if (!array_key_exists($row->spec_id, $this->spec_ids)) {
            $this->spec_ids[$row->spec_id] = 0;
        }
        $this->spec_ids[$row->spec_id]++;
        $this->num++;
    }

    public function getLeaderboardIds()
    {
        $leaderboard_ids = [
            $this->start_leaderboard_id,
            $this->end_leaderboard_id
        ];
        $start = Leaderboard::findOrFail($this->start_leaderboard_id);
        $prev = $start->getPrevious();
        if ($prev) {
            $leaderboard_ids[] = $prev->id;
        }
        $end = Leaderboard::findOrFail($this->end_leaderboard_id);
        $next = $end->getNext();
        if ($next) {
            $leaderboard_ids[] = $next->id;
        }
        $q = Leaderboard::where('id', '>', $start->id)
            ->where('id', '<', $end->id)
            ->where('bracket_id', '=', $start->bracket_id)
            ->where('region_id', '=', $start->region_id);
        $leaderboard_ids = array_merge($leaderboard_ids, $q->pluck('id')->toArray());
        $leaderboard_ids = array_unique(array_filter($leaderboard_ids));
        return $leaderboard_ids;
    }
}
