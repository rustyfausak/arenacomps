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
        $term = OptionsController::getTerm();
        $q = Stat::where('player_id', '=', $player->id)
            ->where('bracket_id', '=', $bracket->id);
        if ($region) {
            $region_id = $region->id;
            $q->whereHas('leaderboard', function ($q) use ($region_id) {
                $q->where('region_id', '=', $region_id);
            });
        }
        $stat = $q->first();

        $bracket_id = $bracket->id;
        $term_id = $term ? $term->id : null;

        $q = Snapshot::with([
                'group',
                'spec'
            ])
            ->select([
                DB::raw('snapshots.*')
            ])
            ->leftJoin('groups AS g', 'snapshots.group_id', '=', 'g.id')
            ->leftJoin('leaderboards AS l', 'g.leaderboard_id', '=', 'l.id')
            ->where('snapshots.player_id', '=', $player_id)
            ->where('l.bracket_id', '=', $bracket->id);
        if ($region) {
            $q->where('l.region_id', '=', $region->id);
        }
        if ($term) {
            $q->where('l.term_id', '=', $term->id);
        }
        $snapshots = $q->orderBy('l.completed_at', 'DESC')
            ->paginate(20);
        return view('player', [
            'player' => $player,
            'stat' => $stat,
            'snapshots' => $snapshots
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
    public function getLeaderboard(Request $request)
    {
        $role = null;
        if ($request->input('class')) {
            $role = Role::find($request->input('class'));
        }
        $leaderboard_ids = $this->_getLeaderboardIds();
        $q = Stat::with('player')
            ->whereIn('leaderboard_id', $leaderboard_ids)
            ->orderBy('rating', 'DESC');
        if ($role) {
            $role_id = $role->id;
            $q->whereHas('player', function ($q) use ($role_id) {
                $q->where('role_id', '=', $role_id);
            });
        }
        $stats = $q->paginate(20);

        return view('leaderboard', [
            'stats' => $stats,
            'role' => $role
        ]);
    }

    /**
     */
    public function getActivity($id = null)
    {
        $leaderboard = Leaderboard::find($id);
        if (!$leaderboard) {
            $id = null;
        }
        $term = OptionsController::getTerm();
        $bracket = OptionsController::getBracket();
        $region = OptionsController::getRegion();
        $q = Snapshot::with([
                'group',
                'spec'
            ])
            ->select([
                DB::raw('snapshots.*')
            ])
            ->leftJoin('groups AS g', 'snapshots.group_id', '=', 'g.id')
            ->leftJoin('leaderboards AS l', 'g.leaderboard_id', '=', 'l.id')
            ->where('l.bracket_id', '=', $bracket->id)
            ->whereRaw('l.completed_at IS NOT NULL');
        if ($id) {
            $q->where('l.id', '=', $id)
                ->orderBy('snapshots.rating', 'DESC');
        }
        else {
            $q->orderBy('l.completed_at', 'DESC');
        }
        if ($region) {
            $q->where('l.region_id', '=', $region->id);
        }
        if ($term) {
            $q->where('l.term_id', '=', $term->id);
        }
        $snapshots = $q->paginate(50);
        return view('activity', [
            'leaderboard' => $leaderboard,
            'snapshots' => $snapshots
        ]);
    }

    /**
     */
    public function getComps(Request $request)
    {
        $region = OptionsController::getRegion();
        $bracket = OptionsController::getBracket();
        $season = OptionsController::getSeason();
        $term = OptionsController::getTerm();

        $sort = $request->input('s');
        if (!in_array($sort, ['avg_rating', 'wins', 'losses', 'ratio'])) {
            $sort = null;
        }
        $sort_dir = (bool) $request->input('d');

        $roles = array_fill(0, $bracket->size, null);
        $specs = array_fill(0, $bracket->size, null);

        foreach ($request->all() as $k => $v) {
            if (preg_match('/^class(\d+)$/', $k, $m)) {
                $role = Role::find($v);
                if ($role) {
                    $roles[$m[1] - 1] = $role;
                }
            }
            if (preg_match('/^spec(\d+)$/', $k, $m)) {
                $spec = Spec::find($v);
                if ($spec) {
                    $specs[$m[1] - 1] = $spec;
                }
            }
        }

        $qs = '';
        foreach ($roles as $i => $role) {
            if ($role) {
                $qs .= '&class' . ($i + 1) . '=' . urlencode($role->id);
            }
        }
        foreach ($specs as $i => $spec) {
            if ($spec) {
                $qs .= '&spec' . ($i + 1) . '=' . urlencode($spec->id);
            }
        }

        $q = Performance::select([
                DB::raw('*'),
                DB::raw('wins / GREATEST(1,losses) AS ratio')
            ])
            ->with('comp')
            ->where('bracket_id', '=', $bracket->id)
            ->where('season_id', '=', $season->id)
            ->whereNotNull('comp_id');
        foreach ($roles as $i => $role) {
            if ($role) {
                if ($specs[$i]) {
                    $role_spec_ids = [$specs[$i]->id];
                }
                else {
                    $role_spec_ids = Spec::select('id')
                        ->where('role_id', '=', $role->id)
                        ->pluck('id')
                        ->toArray();
                }
                $q->whereHas('comp', function ($q) use ($role_spec_ids) {
                    $q->whereIn('spec_id1', $role_spec_ids)
                        ->orWhereIn('spec_id2', $role_spec_ids)
                        ->orWhereIn('spec_id3', $role_spec_ids)
                        ->orWhereIn('spec_id4', $role_spec_ids)
                        ->orWhereIn('spec_id5', $role_spec_ids);
                });
            }
        }
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
        $q->whereNull('team_id');
        if ($sort) {
            $q->orderBy($sort, $sort_dir ? 'ASC' : 'DESC');
        }
        else {
            $q->orderBy('wins', 'DESC')
                ->orderBy('losses', 'ASC')
                ->orderBy('avg_rating', 'DESC');
        }
        $performances = $q->paginate(20);
        return view('comps', [
            'performances' => $performances,
            'roles' => $roles,
            'specs' => $specs,
            'bracket_size' => $bracket->size,
            'sort_dir' => $sort_dir,
            'sort' => $sort,
            'qs' => $qs,
        ]);
    }

    public function getComp($id)
    {
        $comp = Comp::find($id);
        if (!$comp) {
            return redirect()->route('index');
        }
        $region = OptionsController::getRegion();
        $bracket = OptionsController::getBracket();
        $season = OptionsController::getSeason();
        $term = OptionsController::getTerm();
        $q = Performance::where('bracket_id', '=', $bracket->id)
            ->where('season_id', '=', $season->id);
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
        $performance = $q->first();
        return view('comp', [
            'comp' => $comp,
            'performance' => $performance,
            'teams' => $comp->getTeams($season, $region, $term, true)->paginate(20)
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
