<?php

namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;

use App\Models\Bracket;
use App\Models\Comp;
use App\Models\Faction;
use App\Models\Gender;
use App\Models\Leaderboard;
use App\Models\Performance;
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
            return redirect()->route('index');
        }
        $region = OptionsController::getRegion();
        $bracket = OptionsController::getBracket();
        $q = Stat::where('player_id', '=', $player->id)
            ->where('bracket_id', '=', $bracket->id);
        if ($region) {
            $region_id = $region->id;
            $q->whereHas('leaderboard', function ($q) use ($region_id) {
                $q->where('region_id', '=', $region_id);
            });
        }
        $stat = $q->first();
        return view('player', [
            'player' => $player,
            'stat' => $stat,
            'region' => OptionsController::getRegion()
        ]);
    }

    /**
     * @return array
     */
    public function _getLeaderboardIds()
    {
        $season = OptionsController::getSeason();
        $term = OptionsController::getTerm();
        $bracket = OptionsController::getBracket();
        $region = OptionsController::getRegion();
        $regions = [];
        if ($region) {
            $regions[] = $region;
        }
        else {
            $regions = Region::all();
        }
        $leaderboard_ids = [];
        foreach ($regions as $region) {
            $q = Leaderboard::where('bracket_id', '=', $bracket->id)
                ->where('season_id', '=', $season->id)
                ->where('region_id', '=', $region->id);
            if ($term) {
                $q->where('term_id', '=', $term->id);
            }
            $region_leaderboard_ids = $q->whereNotNull('completed_at')
                ->orderBy('created_at', 'DESC')
                ->pluck('id')
                ->toArray();
            $leaderboard_ids = array_merge($leaderboard_ids, $region_leaderboard_ids);
        }
        return $leaderboard_ids;
    }

    /**
     */
    public function getLeaderboard()
    {
        $leaderboard_ids = $this->_getLeaderboardIds();
        $stats = Stat::with('player')
            ->whereIn('leaderboard_id', $leaderboard_ids)
            ->orderBy('rating', 'DESC')
            ->paginate(20);
        return view('leaderboard', [
            'stats' => $stats,
        ]);
    }

    /**
     */
    public function getTest()
    {
        return view('test');
    }

    /**
     */
    public function getComps()
    {
        $region = OptionsController::getRegion();
        $bracket = OptionsController::getBracket();
        $season = OptionsController::getSeason();
        $term = OptionsController::getTerm();
        $q = Performance::where('bracket_id', '=', $bracket->id)
            ->where('season_id', '=', $season->id)
            ->whereNotNull('comp_id');
        if ($region) {
            $q->where('region_id', '=', $region->id);
        }
        else {
            $q->whereNull('region_id');
        }
        if ($term) {
            $q->where('term_id', '=', $term->id);
        }
        else {
            $q->whereNull('term_id');
        }
        $performances = $q->orderBy('skill', 'DESC')
            ->orderBy('wins', 'DESC')
            ->orderBy('losses', 'ASC')
            ->orderBy('avg_rating', 'DESC')
            ->paginate(20);
        return view('comps', [
            'performances' => $performances,
        ]);
    }

    public function getStats()
    {
        $leaderboard_ids = $this->_getLeaderboardIds();

        $datas = [
            'role' => [],
            'spec' => [],
            'race' => [],
        ];

        $datas['role'] = DB::table('stats AS s')
            ->select([
                'p.role_id',
                'r.name AS role_name',
                DB::raw('COUNT(*) AS num'),
                DB::raw('0 AS ranking'),
                DB::raw('0 AS pct'),
            ])
            ->leftJoin('players AS p', 's.player_id', '=', 'p.id')
            ->leftJoin('roles AS r', 'p.role_id', '=', 'r.id')
            ->whereIn('s.leaderboard_id', $leaderboard_ids)
            ->groupBy('p.role_id')
            ->orderBy('num', 'DESC')
            ->get()
            ->toArray();

        $datas['spec'] = DB::table('stats AS s')
            ->select([
                'p.role_id',
                'p.spec_id',
                'r.name AS role_name',
                'sp.name AS spec_name',
                DB::raw('COUNT(*) AS num'),
                DB::raw('0 AS ranking'),
                DB::raw('0 AS pct'),
            ])
            ->leftJoin('players AS p', 's.player_id', '=', 'p.id')
            ->leftJoin('specs AS sp', 'p.spec_id', '=', 'sp.id')
            ->leftJoin('roles AS r', 'sp.role_id', '=', 'r.id')
            ->whereIn('s.leaderboard_id', $leaderboard_ids)
            ->groupBy('p.spec_id')
            ->orderBy('num', 'DESC')
            ->get()
            ->toArray();

        $datas['race'] = DB::table('stats AS s')
            ->select([
                'p.race_id',
                'r.name AS race_name',
                DB::raw('COUNT(*) AS num'),
                DB::raw('0 AS ranking'),
                DB::raw('0 AS pct'),
            ])
            ->leftJoin('players AS p', 's.player_id', '=', 'p.id')
            ->leftJoin('races AS r', 'p.race_id', '=', 'r.id')
            ->whereIn('s.leaderboard_id', $leaderboard_ids)
            ->groupBy('p.race_id')
            ->orderBy('num', 'DESC')
            ->get()
            ->toArray();

        foreach ($datas as $k => $data) {
            $total = 0;
            foreach ($data as $row) {
                $total += $row->num;
            }
            $i = 1;
            foreach ($data as $row) {
                $row->ranking = $i++;
                if ($total) {
                    $row->pct = round($row->num / $total * 100, 1);
                }
            }
            $datas[$k] = $data;
        }

        return view('stats', [
            'role_data' => $datas['role'],
            'spec_data' => $datas['spec'],
            'race_data' => $datas['race'],
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
