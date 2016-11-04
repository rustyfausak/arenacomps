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
        return [
            ['region', 'r', InputOption::VALUE_OPTIONAL, 'The region.', 'us'],
            ['bracket', 'b', InputOption::VALUE_OPTIONAL, 'The bracket.', '3v3'],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $region = Region::where('name', 'LIKE', $this->option('region'))->first();
            if (!$region) {
                throw new \Exception("Invalid region '{$this->option('region')}'.");
            }
            $bracket = Bracket::where('name', 'LIKE', $this->option('bracket'))->first();
            if (!$bracket) {
                throw new \Exception("Invalid bracket '{$this->option('bracket')}'.");
            }
            $leaderboard_ids = DB::table('leaderboards')
                ->whereRaw('created_at > (NOW() - INTERVAL 1 DAY)')
                ->where('bracket_id', '=', $bracket->id)
                ->where('region_id', '=', $region->id)
                ->pluck('id')
                ->toArray();
            $player_ids = DB::table('snapshots AS s')
                ->select('s.player_id')
                ->whereIn('s.leaderboard_id', $leaderboard_ids)
                ->groupBy('s.player_id')
                ->pluck('s.player_id')
                ->toArray();
            foreach ($player_ids as $player_id) {
                $this->info("Player #{$player_id}");

                $player = Player::find($player_id);

                // Find related players (other players who appeared in the same leaderboards with
                // the same win/loss)
                $sql = "
                    SELECT
                        `leaderboard_id`,
                        GROUP_CONCAT(`player_id`) AS `player_ids`
                    FROM `snapshots`
                    WHERE `leaderboard_id` IN (" . implode(',', $leaderboard_ids) . ")
                    GROUP BY
                        `leaderboard_id`,
                        `wins`,
                        `losses`
                    HAVING {$player_id} IN (`player_ids`)";
                $results = DB::select($sql);
                if (sizeof($results) < self::MIN_SIMILAR_SNAPSHOTS) {
                    // Cant draw any conclusions when there are too few data points for this player
                    $this->line("\tnot enough data");
                    continue;
                }

                // Count up the occurences of related players
                $rel_map = [];
                foreach ($results as $result) {
                    $ss_player_ids = explode(',', $result->player_ids);
                    foreach ($ss_player_ids as $ss_player_id) {
                        if ($ss_player_id == $player_id) {
                            continue;
                        }
                        if (!array_key_exists($ss_player_id, $rel_map)) {
                            $rel_map[$ss_player_id] = 0;
                        }
                        $rel_map[$ss_player_id]++;
                    }
                }
                arsort($rel_map);
                $rel_map = array_slice($rel_map, 0, $bracket->size + 1, true);

                // Remove any related players below the limit
                $remove_player_ids = [];
                foreach ($rel_map as $rel_player_id => $rel_count) {
                    if ($rel_count < self::MIN_SIMILAR_SNAPSHOTS) {
                        $remove_player_ids[$rel_player_id] = 1;
                    }
                }
                $rel_map = array_diff_key($rel_map, $remove_player_ids);
                print_r($rel_map);
                $rel_player_ids = array_keys($rel_map);

                // Count the number of times each other player gets put on a team
                // with this player
                $team_map = [];
                foreach ($results as $result) {
                    $team_player_ids = [$player_id];
                    $ss_player_ids = explode(',', $result->player_ids);
                    $full = false;
                    foreach ($rel_player_ids as $rel_player_id) {
                        if (in_array($rel_player_id, $ss_player_ids)) {
                            $team_player_ids[] = $rel_player_id;
                            if (sizeof($team_player_ids) == $bracket->size) {
                                $full = true;
                                break;
                            }
                        }
                    }
                    if ($full) {
                        foreach ($team_player_ids as $team_player_id) {
                            if (!array_key_exists($team_player_id, $team_map)) {
                                $team_map[$team_player_id] = 0;
                            }
                            $team_map[$team_player_id]++;
                        }
                    }
                }

                // Remove any team players below the limit
                $remove_player_ids = [];
                foreach ($rel_player_ids as $rel_player_id) {
                    if (!array_key_exists($rel_player_id, $team_map) || $team_map[$rel_player_id] < self::MIN_SIMILAR_SNAPSHOTS) {
                        $this->line("removed {$rel_player_id} for low team matches.");
                        $remove_player_ids[] = $rel_player_id;
                    }
                }
                $rel_player_ids = array_diff($rel_player_ids, $remove_player_ids);

                $remove_player_ids = [];
                foreach ($rel_player_ids as $rel_player_id) {
                    $rel_player = Player::find($rel_player_id);
                    if (!$rel_player || $rel_player->faction_id != $player->faction_id) {
                        $this->line("removed {$rel_player_id} for wrong faction.");
                        $remove_player_ids[] = $rel_player_id;
                    }
                }
                $rel_player_ids = array_diff($rel_player_ids, $remove_player_ids);

                // Try to determine the team
                foreach ($results as $result) {
                    $team_player_ids = [$player_id];
                    $ss_player_ids = explode(',', $result->player_ids);
                    $full = false;
                    foreach ($rel_player_ids as $rel_player_id) {
                        if (in_array($rel_player_id, $ss_player_ids)) {
                            $team_player_ids[] = $rel_player_id;
                            if (sizeof($team_player_ids) == $bracket->size) {
                                $full = true;
                                break;
                            }
                        }
                    }
                    if ($full) {
                        $this->linkSnapshots($result->leaderboard_id, $team_player_ids);
                    }
                }
            }
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
    }

    /**
     * @param int   $leaderboard_id
     * @param array $player_ids
     */
    public function linkSnapshots($leaderboard_id, $player_ids)
    {
        sort($player_ids);
        $team = Team::getOrBuild($player_ids);
        $snapshots = Snapshot::where('leaderboard_id', '=', $leaderboard_id)
            ->whereIn('player_id', $player_ids)
            ->get();
        $spec_ids = [];
        foreach ($snapshots as $snapshot) {
            $spec_ids[] = $snapshot->spec_id;
        }
        sort($spec_ids);
        $comp = Comp::getOrBuild($spec_ids);
        foreach ($snapshots as $snapshot) {
            $snapshot->team_id = $team->id;
            $snapshot->comp_id = $comp->id;
            $snapshot->save();
        }
    }
}
