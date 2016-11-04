<?php

namespace App\Http\Controllers;

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

class IndexController extends Controller
{
    /**
     * @param int $player_id
     */
    public function getPlayer($player_id)
    {
        $player = Player::find($player_id);
        if (!$player) {
            return route('index');
        }
        return view('player', [
            'player' => $player
        ]);
    }

    /**
     * @param string      $bracket_name
     * @param string|null $region_name  Set to null for all
     */
    public function getLeaderboard($bracket_name = '3v3', $region_name = null)
    {
        $bracket = Bracket::where('name', '=', $bracket_name)->first();
        if (!$bracket) {
            return route('index');
        }
        $region = null;
        if ($region_name) {
            $region = Region::where('name', 'LIKE', $region_name)->first();
            if (!$region) {
                return route('index');
            }
        }
        $q = Leaderboard::where('bracket_id', '=', $bracket->id);
        if ($region) {
            $q->where('region_id', '=', $region->id);
        }
        $q->orderBy('created_at', 'DESC')
            ->limit(1);
        $leaderboard_ids = $q->pluck('id')->toArray();
        $stats = Stat::with('player')
            ->whereIn('leaderboard_id', $leaderboard_ids)
            ->orderBy('rating', 'DESC')
            ->paginate(20);
        return view('leaderboard', [
            'bracket' => $bracket,
            'stats' => $stats,
        ]);
    }

    public function getComps()
    {
        $comps = Comp::all();
        return view('comps', [
            'comps' => $comps
        ]);
    }
}
