<?php

namespace App\Http\Controllers;

use Session;
use Illuminate\Http\Request;

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
     */
    public function getLeaderboard()
    {
        $bracket = OptionsController::getBracket();
        $region = OptionsController::getRegion();
        $q = Leaderboard::where('bracket_id', '=', $bracket->id);
        if ($region) {
            $q->where('region_id', '=', $region->id);
        }
        $q->whereNotNull('completed_at')
            ->orderBy('created_at', 'DESC')
            ->limit(1);
        $leaderboard_ids = $q->pluck('id')->toArray();
        $stats = Stat::with('player')
            ->whereIn('leaderboard_id', $leaderboard_ids)
            ->orderBy('rating', 'DESC')
            ->paginate(20);
        return view('leaderboard', [
            'stats' => $stats,
        ]);
    }

    /**
     * @param string $bracket_name
     */
    public function getComps($bracket_name = '3v3')
    {
        $bracket = Bracket::where('name', '=', $bracket_name)->first();
        if (!$bracket) {
            return route('index');
        }
        $comps = Comp::all();
        return view('comps', [
            'comps' => $comps,
        ]);
    }

    public function postSetOptions(Request $request)
    {
        OptionsController::setBracket($request->input('bracket'));
        OptionsController::setRegion($request->input('region'));
        OptionsController::setSeason($request->input('season'));
        OptionsController::setTerm($request->input('term'));
        return back();
    }
}
