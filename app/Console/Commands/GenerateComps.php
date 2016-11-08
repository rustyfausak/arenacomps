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
    const MIN_SIMILAR_SNAPSHOTS = 10;

    /**
     * The minimum number of times a player must be put on a team with another player before
     * the team can be finalized.
     */
    const MIN_TEAMMATE_COUNT = 5;

    /**
     * A number between 0 and 1 representing the amount of weight to put on ratings being similar.
     * Two players within 30 rating of each other will have an additional num_games_similar * rating_weight
     * applied.
     */
    const RATING_WEIGHT = 1;

    /**
     * Cache for player ID in group ID
     */
    protected $pgcache;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->pgcache = [];
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
            $q = DB::table('snapshots AS s')
                ->leftJoin('groups AS g', 's.group_id', '=', 'g.id')
                ->leftJoin('leaderboards AS l', 'g.leaderboard_id', '=', 'l.id')
                ->leftJoin('brackets AS b', 'l.bracket_id', '=', 'b.id')
                ->select([
                    's.player_id',
                    'l.bracket_id',
                    'b.size',
                    DB::raw('COUNT(*) AS num'),
                    DB::raw('GROUP_CONCAT(s.id) AS snapshot_ids'),
                    DB::raw('GROUP_CONCAT(s.group_id) AS group_ids'),
                ])
                ->where(function ($q) {
                    $q->whereNull('s.team_id')
                        ->orWhereNull('s.comp_id');
                })
                ->whereNotNull('l.completed_at')
                ->whereRaw('l.completed_at > (NOW() - INTERVAL 1 WEEK)')
                ->groupBy('s.player_id', 'l.bracket_id')
                ->orderBy('s.rating', 'DESC');
            $results = $q->get();
            foreach ($results as $result) {
                if ($result->num < self::MIN_SIMILAR_SNAPSHOTS) {
                    // Cannot match when too few snapshots
                    continue;
                }
                $this->generate(
                    $result->player_id,
                    $result->size,
                    explode(',', $result->snapshot_ids),
                    explode(',', $result->group_ids)
                );
            }
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }

    /**
     * @param int $group_id
     * @return array
     */
    public function playerIdsInGroupId($group_id)
    {
        if (array_key_exists($group_id, $this->pgcache)) {
            return $this->pgcache;
        }
        $this->pgcache[$group_id] = DB::table('snapshots')
            ->select('player_id')
            ->where('group_id', '=', $group_id)
            ->orderBy('rating', 'DESC')
            ->pluck('player_id')
            ->toArray();
        return $this->pgcache[$group_id];
    }

    /**
     * @param int   $player_id
     * @param int   $bracket_size
     * @param array $snapshot_ids
     * @param array $group_ids
     */
    public function generate($player_id, $bracket_size, $snapshot_ids, $group_ids)
    {
        $this->info("Player #{$player_id} / bracket size: {$bracket_size} / # snapshots: " . sizeof($snapshot_ids));

        $player_snapshots = Snapshot::whereIn('id', $snapshot_ids)->get();

        // Find players in the same groups as the considered snapshots and order by occurences
        $other_player_data = DB::table('snapshots')
            ->select([
                'player_id',
                DB::raw('COUNT(*) AS num'),
                DB::raw('0 AS teammate_count'),
                DB::raw('AVG(rating) AS avg_rating'),
                DB::raw('0 AS weight'),
            ])
            ->whereIn('group_id', $group_ids)
            ->where('player_id', '!=', $player_id)
            ->groupBy('player_id')
            ->orderBy('num', 'DESC')
            ->orderBy('avg_rating', 'DESC')
            ->limit($bracket_size + 1)
            ->having('num', '>', self::MIN_SIMILAR_SNAPSHOTS)
            ->get()
            ->toArray();
        //$this->line("\tFound " . sizeof($other_player_data) . " related players.");
        if (sizeof($other_player_data) < $bracket_size - 1) {
            // Not enough players to make a team
            //$this->line("\tnot enough to make a team [1]..");
            return;
        }

        foreach ($player_snapshots as $snapshot) {
            $snapshot->team_player_ids = [];

            $sort = [];
            foreach ($other_player_data as $k => $data) {
                $diff = max(30, min(3000, abs($snapshot->rating - $data->avg_rating)));
                $data->diff = $diff;
                $factor = round(1 / ($diff / 3000)); // between 1 and 100
                $data->factor = $factor;
                $data->weight = $data->num + round($data->num * self::RATING_WEIGHT * ($factor / 100), 2);
                $sort[$k] = $data->weight;
            }
            array_multisort($sort, SORT_DESC, $other_player_data);

            // Find players that are options for teammates for this snapshot
            // (should be a subset of $other_player_data)
            $related_player_ids = $this->playerIdsInGroupId($snapshot->group_id);
            $team_player_ids = [$player_id];
            foreach ($other_player_data as $data) {
                if (in_array($data->player_id, $related_player_ids)) {
                    $team_player_ids[] = $data->player_id;
                }
            }

            // $team_player_ids contains the matching player IDs ordered from most occurences to least

            // Trim the team size to the bracket size
            $team_player_ids = array_slice($team_player_ids, 0, $bracket_size);
            if (sizeof($team_player_ids) != $bracket_size) {
                // didn't find enough team members
                //$this->line("\tnot enough to make a team [2]..");
                continue;
            }

            // Found enough team members, add teammate count to player data
            foreach ($other_player_data as $data) {
                if (in_array($data->player_id, $team_player_ids)) {
                    $data->teammate_count++;
                }
            }

            // Save the teammate IDs to the snapshot
            $snapshot->team_player_ids = $team_player_ids;
        }

        // Now we know how many times each player in $other_player_data was put on a team with this player.
        // We can require that each player much have a minimum number of teammate_count in order to
        // actually be placed on a team.

        // Remove teammates with too few count
        $remove_keys = [];
        foreach ($other_player_data as $k => $data) {
            if ($data->teammate_count < self::MIN_TEAMMATE_COUNT) {
                $remove_keys[$k] = 1;
            }
        }
        $other_player_data = array_diff_key($other_player_data, $remove_keys);

        if (sizeof($other_player_data) < $bracket_size - 1) {
            // Not enough players to make a team
            //$this->line("\tnot enough to make a team [3]..");
            return;
        }

        foreach ($player_snapshots as $snapshot) {
            if (!sizeof($snapshot->team_player_ids)) {
                continue;
            }
            $all = true;
            foreach ($snapshot->team_player_ids as $team_player_id) {
                if ($team_player_id == $player_id) {
                    continue;
                }
                $found = false;
                foreach ($other_player_data as $data) {
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
        //$this->line("\tbuild " . implode(',', $team_player_ids));
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
