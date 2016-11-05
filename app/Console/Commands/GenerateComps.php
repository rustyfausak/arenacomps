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

class GenerateComps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'comps:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the comp data.';

    /**
     * The minimum number of snapshots that must be similar in order for two
     * players to be considered to be on the same team.
     */
    const MIN_SIMILAR_SNAPSHOTS = 3;

    /**
     * The number of seconds in either direction to look for other snapshots for a player.
     */
    const CONSIDER_SNAPSHOTS_WITHIN_SECONDS = 60 * 60 * 24 * 7;

    /**
     * The minimum number of times a player must be put on a team with another player before
     * the team can be finalized.
     */
    const MIN_TEAMMATE_COUNT = 3;

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
            // Find snapshots that need team or comp generated
            $snapshots = Snapshot::with([
                    'player',
                    'group',
                    'group.leaderboard',
                    'group.leaderboard.bracket',
                ])
                ->whereNull('team_id')
                ->orWhereNull('group_id')
                ->get();
            foreach ($snapshots as $snapshot) {
                $this->generate($snapshot);
            }
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }

    /**
     * @param Snapshot $snapshot
     */
    public function generate(Snapshot $main_snapshot)
    {
        $this->info("Snapshot #{$main_snapshot->id} - Player #{$main_snapshot->player_id}");

        if ($main_snapshot->team_id && $main_snapshot->comp_id) {
            // Has already been determined by another snapshot
            $this->line("\tAlready determined.");
            return;
        }

        $bracket_size = $main_snapshot->group->leaderboard->bracket->size;
        $timestamp = strtotime($main_snapshot->group->leaderboard->created_at);
        $min_datetime = date("Y-m-d H:i:s", $timestamp - self::CONSIDER_SNAPSHOTS_WITHIN_SECONDS);
        $max_datetime = date("Y-m-d H:i:s", $timestamp + self::CONSIDER_SNAPSHOTS_WITHIN_SECONDS);

        // Find the snapshots to consider
        $snapshot_ids = DB::table('snapshots AS s')
            ->select('s.id')
            ->leftJoin('groups AS g', 's.group_id', '=', 'g.id')
            ->leftJoin('leaderboards AS l', 'g.leaderboard_id', '=', 'l.id')
            ->where('s.player_id', '=', $main_snapshot->player_id)
            ->whereRaw("l.created_at > '{$min_datetime}'")
            ->whereRaw("l.created_at < '{$max_datetime}'")
            ->pluck('s.id')
            ->toArray();
        $this->line("\tFound " . sizeof($snapshot_ids) . " snapshots to consider.");
        if (!sizeof($snapshot_ids)) {
            return;
        }
        $snapshots = Snapshot::whereIn('id', $snapshot_ids)->get();

        // Find the group IDs to consider
        $group_ids = [];
        foreach ($snapshots as $snapshot) {
            $group_ids[] = $snapshot->group_id;
        }

        // Find players in the same groups as the considered snapshots and order by occurences
        $player_data = DB::table('snapshots')
            ->select([
                'player_id',
                DB::raw('COUNT(*) AS num'),
                DB::raw('0 AS teammate_count'),
            ])
            ->whereIn('group_id', $group_ids)
            ->where('player_id', '!=', $main_snapshot->player_id)
            ->groupBy('player_id')
            ->orderBy('num', 'DESC')
            ->limit($bracket_size + 1)
            ->having('num', '>', self::MIN_SIMILAR_SNAPSHOTS)
            ->get()
            ->toArray();
        $this->line("\tFound " . sizeof($player_data) . " related players.");
        if (sizeof($player_data) < $bracket_size - 1) {
            // Not enough players to make a team
            return;
        }

        // Each snapshot for this player should consider only the players in $player_data

        foreach ($snapshots as $snapshot) {
            $snapshot->team_player_ids = [];

            // Find players that are options for teammates for this snapshot
            // (should be a subset of $player_data)
            $related_player_ids = DB::table('snapshots')
                ->select('player_id')
                ->where('group_id', '=', $snapshot->group_id)
                ->where('player_id', '!=', $main_snapshot->player_id)
                ->pluck('player_id')
                ->toArray();
            $team_player_ids = [$main_snapshot->player_id];
            foreach ($player_data as $data) {
                if (in_array($data->player_id, $related_player_ids)) {
                    $team_player_ids[] = $data->player_id;
                }
            }

            // $team_player_ids contains the matching player IDs ordered from most occurences to least

            // Trim the team size to the bracket size
            $team_player_ids = array_slice($team_player_ids, 0, $bracket_size);
            if (sizeof($team_player_ids) != $bracket_size) {
                // didn't find enough team members
                continue;
            }


            // Found enough team members, add teammate count to player data
            foreach ($player_data as $data) {
                if (in_array($data->player_id, $team_player_ids)) {
                    $data->teammate_count++;
                }
            }

            // Save the teammate IDs to the snapshot
            $snapshot->team_player_ids = $team_player_ids;
        }

        // Now we know how many times each player in $player_data was put on a team with this player.
        // We can require that each player much have a minimum number of teammate_count in order to
        // actually be placed on a team.

        // Remove teammates with too few count
        $remove_keys = [];
        foreach ($player_data as $k => $data) {
            if ($data->teammate_count < self::MIN_TEAMMATE_COUNT) {
                $remove_keys[] = $k;
            }
        }
        $player_data = array_diff_key($player_data, $remove_keys);

        if (sizeof($player_data) < $bracket_size - 1) {
            // Not enough players to make a team
        }

        print_r($player_data);

        foreach ($snapshots as $snapshot) {
            if (!sizeof($snapshot->team_player_ids)) {
                continue;
            }
            $all = true;
            foreach ($snapshot->team_player_ids as $team_player_id) {
                if ($team_player_id == $main_snapshot->player_id) {
                    continue;
                }
                $found = false;
                foreach ($player_data as $data) {
                    if ($data->player_id == $team_player_id) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $all = false;
                    break;
                }
            }
            if (!$all) {
                // Not all team players were valid teammates
                continue;
            }
            $this->build($snapshot, $snapshot->team_player_ids);
        }
    }

    /**
     * @param Snapshot $snapshot
     * @param array of int $team_player_ids
     */
    public function build(Snapshot $snapshot, $team_player_ids)
    {
        $team = Team::getOrBuild($team_player_ids);
        $snapshots = Snapshot::where('group_id', '=', $snapshot->group_id)
            ->whereIn('player_id', $team_player_ids)
            ->get();
        $spec_ids = [];
        foreach ($snapshots as $snapshot) {
            $spec_ids[] = $snapshot->spec_id;
        }
        $comp = Comp::getOrBuild($spec_ids);
        foreach ($snapshots as $snapshot) {
            $snapshot->team_id = $team->id;
            $snapshot->comp_id = $comp->id;
            $snapshot->save();
        }
    }
}
