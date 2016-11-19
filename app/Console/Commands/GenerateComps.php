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

class GenerateComps extends Command
{
    protected $name = 'comps:generate';
    protected $description = 'Generate the comp data.';

    const MIN_TEAMMATE_COUNT = 10;
    const MIN_CONSECUTIVE = 5;
    const ALLOW_MISS = true;
    const MAX_RATING_DIFF = 250;
    const MAX_TEAMMATES = 10;

    public function getOptions()
    {
        return [
            ['player_id', 'p', InputOption::VALUE_OPTIONAL, 'The player ID.', null],
        ];
    }

    public function handle()
    {
        try {
            $bracket = Bracket::getDefault();
            if ($this->option('player_id')) {
                $this->processPlayer($bracket, Player::findOrFail($this->option('player_id')));
            }
            else {
                $this->processBracket($bracket);
            }
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }

    public function processBracket(Bracket $bracket)
    {
        print "Bracket {$bracket}\n";
        // Find relevant players
        $player_ids = DB::table('snapshots AS s')
            ->leftJoin('groups AS g', 'g.id', '=', 's.group_id')
            ->leftJoin('leaderboards AS l', 'l.id', '=', 'g.leaderboard_id')
            ->where('l.bracket_id', '=', $bracket->id)
            ->whereNull('s.team_id')
            ->groupBy('s.player_id')
            ->orderBy('s.player_id', 'ASC')
            ->pluck('s.player_id');
        $all_total = 0;
        $all_w_players = 0;
        $all_matched = 0;
        foreach ($player_ids as $k => $player_id) {
            $player = Player::find($player_id);
            print sprintf("%20s", $player) . "\t";
            list($total, $w_players, $matched) = $this->processPlayer($bracket, $player);
            print $total . "\t";
            if ($total) {
                print sprintf("%01.2f", round(($w_players / $total) * 100, 2)) . "% (";
                print sprintf("%01.2f", round(($matched / $total) * 100, 2)) . "%)\t";
            }
            $all_total += $total;
            $all_w_players += $w_players;
            $all_matched += $matched;
            if ($all_total) {
                print sprintf("%01.2f", round(($all_w_players / $all_total) * 100, 2)) . "% (";
                print sprintf("%01.2f", round(($all_matched / $all_total) * 100, 2)) . "%)\t";
            }
            print $k . " / " . sizeof($player_ids) . "\t" . sprintf("%01.2f", round($k / sizeof($player_ids), 2)) . "%";
            print "\n";
        }
    }

    /**
     * @param Bracket $bracket
     * @param Player  $player
     */
    public function processPlayer(Bracket $bracket, Player $player)
    {
        Team::where('player_id1', '=', $player->id)
            ->orWhere('player_id2', '=', $player->id)
            ->orWhere('player_id3', '=', $player->id)
            ->delete();
        Snapshot::where('player_id', '=', $player->id)
            ->update([
                'team_id' => null,
                'comp_id' => null
            ]);
        // Get all the groups that this player has participated in
        $group_ids = DB::table('snapshots AS s')
            ->select('g.id')
            ->leftJoin('groups AS g', 'g.id', '=', 's.group_id')
            ->leftJoin('leaderboards AS l', 'l.id', '=', 'g.leaderboard_id')
            ->where('l.bracket_id', '=', $bracket->id)
            ->where('s.player_id', '=', $player->id)
            ->orderBy('g.id', 'ASC')
            ->pluck('g.id');

        if (!sizeof($group_ids)) {
            print "x\n";
            return;
        }

        // Now we want to find players that matched the groups this player participated in. Furthermore, we want to
        // place more importance on players that match _consecutive_ groups, since we're interested in teams and teams
        // usually play together consecutively.

        $cur = [];
        $gid_to_pids = [];

        // If self::ALLOW_MISS is true, we allow a single group miss on a consecutive streak
        $to_chop = [];

        foreach ($group_ids as $group_id) {
            // Find all the other players that exactly matched this group
            $gid_to_pids[$group_id] = [];
            $player_ids = DB::table('snapshots AS s')
                ->select('s.player_id')
                ->leftJoin('groups AS g', 'g.id', '=', 's.group_id')
                ->leftJoin('leaderboards AS l', 'l.id', '=', 'g.leaderboard_id')
                ->where('g.id', '=', $group_id)
                ->where('s.player_id', '!=', $player->id)
                ->orderBY('s.player_id', 'ASC')
                ->pluck('s.player_id')
                ->toArray();
            $new = [];
            foreach ($player_ids as $player_id) {
                if (!array_key_exists($player_id, $cur)) {
                    $new[$player_id] = [$group_id];
                }
                else {
                    $new[$player_id] = $cur[$player_id];
                    $new[$player_id][] = $group_id;
                }
            }
            $diff = array_diff_key($cur, $new);
            $cur = $new;
            foreach ($diff as $player_id => $gids) {
                if (self::ALLOW_MISS && !in_array($player_id, $to_chop)) {
                    $cur[$player_id] = array_merge($gids, [$group_id]);
                    continue;
                }
                if (sizeof($gids) <= self::MIN_CONSECUTIVE) {
                    continue;
                }
                foreach ($gids as $gid) {
                    $gid_to_pids[$gid][] = $player_id;
                }
            }
            $to_chop = array_keys($diff);
        }

        // Count up the occurences of each player
        $pid_count = [];
        foreach ($gid_to_pids as $gid => $pids) {
            foreach ($pids as $pid) {
                if (!array_key_exists($pid, $pid_count)) {
                    $pid_count[$pid] = 0;
                }
                $pid_count[$pid]++;
            }
        }

        // Sort by number of occurences
        arsort($pid_count);

        // Limit to the maximum number of teammates
        $pid_count = array_slice($pid_count, 0, self::MAX_TEAMMATES, true);

        // Remove teammates with less than the required number of group matches
        $remove = [];
        foreach ($pid_count as $pid => $count) {
            if ($count < self::MIN_TEAMMATE_COUNT) {
                $remove[$pid] = 1;
            }
        }
        $pid_count = array_diff_key($pid_count, $remove);

        // Filter out players that are no longer valid from the gid_to_pids map
        $remove = [];
        foreach ($gid_to_pids as $gid => $pids) {
            $gid_to_pids[$gid] = array_intersect($pids, array_keys($pid_count));
            if (sizeof($gid_to_pids[$gid]) < $bracket->size - 1) {
                unset($gid_to_pids[$gid]);
            }
        }

        $teamid_to_snapids = [];
        $compid_to_snapids = [];
        $pid_to_sid = [];
        $matched = 0;
        foreach ($gid_to_pids as $gid => $pids) {
            //print $gid . "\t" . implode(',', $pids) . "\t";
            $team_pids = null;
            if (sizeof($pids) == $bracket->size - 1) {
                // exact size match, form the team
                //print 'E';
                $team_pids = $pids;
            }
            else {
                $tmp_gids = array_keys($gid_to_pids);
                $at_key = array_search($gid, $tmp_gids);
                $before_pids = [];
                $after_pids = [];
                if ($at_key !== false) {
                    if ($at_key > 0) {
                        $before_pids = $gid_to_pids[$tmp_gids[$at_key - 1]];
                    }
                    if ($at_key + 1 < sizeof($tmp_gids)) {
                        $after_pids = $gid_to_pids[$tmp_gids[$at_key + 1]];
                    }
                }
                $sort = [];
                foreach ($pids as $k => $pid) {
                    $sort[$k] = $pid_count[$pid];
                }
                array_multisort($sort, SORT_DESC, $pids);
                $check_pids = array_slice($pids, 0, $bracket->size - 1);
                $all = true;
                foreach ($check_pids as $pid) {
                    if (!in_array($pid, $before_pids) || !in_array($pid, $after_pids)) {
                        $all = false;
                        break;
                    }
                }
                if ($all) {
                    //print 'A';
                    $team_pids = $check_pids;
                }
            }
            if ($team_pids) {
                $team_pids[] = $player->id;
                $snap_ids = [];
                $matched++;
                $q = Snapshot::select([
                        'id',
                        'spec_id',
                        'player_id',
                    ])
                    ->whereIn('player_id', $team_pids)
                    ->where('group_id', '=', $gid);
                foreach ($q->get() as $row) {
                    $snap_ids[] = $row->id;
                    $pid_to_sid[$row->player_id] = $row->spec_id;
                }
                $spec_ids = [];
                foreach ($team_pids as $pid) {
                    if (array_key_exists($pid, $pid_to_sid)) {
                        $spec_ids[] = $pid_to_sid[$pid];
                    }
                }
                if (sizeof($team_pids) == $bracket->size && sizeof($spec_ids) == $bracket->size) {
                    //print "\t+";
                    $team = Team::getOrBuild($team_pids);
                    $comp = Comp::getOrBuild($spec_ids);
                    if (!array_key_exists($team->id, $teamid_to_snapids)) {
                        $teamid_to_snapids[$team->id] = [];
                    }
                    if (!array_key_exists($comp->id, $compid_to_snapids)) {
                        $compid_to_snapids[$comp->id] = [];
                    }
                    foreach ($snap_ids as $snap_id) {
                        $teamid_to_snapids[$team->id][] = $snap_id;
                        $compid_to_snapids[$comp->id][] = $snap_id;
                    }
                }
                else {
                    //print "\t-";
                }
            }
            //print "\n";
        }

        foreach ($teamid_to_snapids as $team_id => $snap_ids) {
            Snapshot::whereIn('id', $snap_ids)
                ->update(['team_id' => $team_id]);
        }

        foreach ($compid_to_snapids as $comp_id => $snap_ids) {
            Snapshot::whereIn('id', $snap_ids)
                ->update(['comp_id' => $comp_id]);
        }

        $total = sizeof($group_ids);
        $w_players = sizeof($gid_to_pids);
        //print_r($gid_to_pids);
        //print_r($pid_count);
        //exit;
        return [$total, $w_players, $matched];

    }
}
