<?php

namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;

use App\OptionsManager;
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
        $om = OptionsManager::build();
        $q = Stat::where('player_id', '=', $player->id)
            ->where('bracket_id', '=', $om->bracket->id);
        if ($om->region) {
            $region_id = $om->region->id;
            $q->whereHas('leaderboard', function ($q) use ($region_id) {
                $q->where('region_id', '=', $region_id);
            });
        }
        $stat = $q->first();

        $bracket_id = $om->bracket->id;
        $term_id = $om->term ? $om->term->id : null;

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
            ->where('l.bracket_id', '=', $om->bracket->id);
        if ($om->region) {
            $q->where('l.region_id', '=', $om->region->id);
        }
        if ($om->term) {
            $q->where('l.term_id', '=', $om->term->id);
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
        $om = OptionsManager::build();
        $leaderboard_ids = [];
        foreach ($om->regions as $region) {
            $q = Leaderboard::where('bracket_id', '=', $om->bracket->id)
                ->where('season_id', '=', $om->season->id)
                ->where('region_id', '=', $region->id);
            if ($om->term) {
                $q->where('term_id', '=', $om->term->id);
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
     * Shows activity for the leaderboard with id=$id. If no $id is given, shows all activity.
     *
     * @param int $id
     */
    public function getActivity($id = null)
    {
        $leaderboard = null;
        if ($id) {
            $q = Leaderboard::where('id', '=', $id)
                ->where('bracket_id', '=', $this->om->bracket->id)
                ->where('season_id', '=', $this->om->season->id)
                ->whereNotNull('completed_at');
            if ($this->om->region) {
                $q->where('region_id', '=', $this->om->region->id);
            }
            if ($this->om->term) {
                $q->where('term_id', '=', $this->om->term->id);
            }
            $leaderboard = $q->first();
            if (!$leaderboard) {
                return redirect()->route('index');
            }
        }
        $q = Snapshot::with([
                'group',
                'spec',
                'spec.role',
                'player'
            ])
            ->leftJoin('groups AS g', 'snapshots.group_id', '=', 'g.id')
            ->leftJoin('leaderboards AS l', 'g.leaderboard_id', '=', 'l.id')
            ->where('l.bracket_id', '=', $this->om->bracket->id)
            ->whereRaw('l.completed_at IS NOT NULL');
        if ($leaderboard) {
            $q->where('l.id', '=', $leaderboard->id)
                ->orderBy('snapshots.rating', 'DESC');
        }
        else {
            $q->orderBy('l.completed_at', 'DESC');
        }
        if ($this->om->region) {
            $q->where('l.region_id', '=', $this->om->region->id);
        }
        if ($this->om->term) {
            $q->where('l.term_id', '=', $this->om->term->id);
        }
        $snapshots = $q->paginate(30);
        return view('activity', [
            'leaderboard' => $leaderboard,
            'snapshots' => $snapshots
        ]);
    }

    /**
     */
    public function getComps(Request $request)
    {
        $om = OptionsManager::build();

        $sort = $request->input('s');
        if (!in_array($sort, ['avg_rating', 'wins', 'losses', 'ratio', 'num_teams'])) {
            $sort = null;
        }
        $sort_dir = (bool) $request->input('d');

        $roles = array_fill(0, $om->bracket->size, null);
        $specs = array_fill(0, $om->bracket->size, null);

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
            ->where('bracket_id', '=', $om->bracket->id)
            ->where('season_id', '=', $om->season->id)
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
        if ($om->region) {
            $q->where('region_id', '=', $om->region->id);
        }
        else {
            $q->whereNull('region_id');
        }
        if ($om->term) {
            $q->where('term_id', '=', $om->term->id);
        }
        else {
            $q->whereNull('term_id');
        }
        $q->whereNull('team_id');
        if (!$sort) {
            $sort = 'wins';
            $sort_dir = 0;
        }
        $q->orderBy($sort, $sort_dir ? 'ASC' : 'DESC');
        $performances = $q->paginate(20);
        return view('comps', [
            'performances' => $performances,
            'roles' => $roles,
            'specs' => $specs,
            'bracket_size' => $om->bracket->size,
            'sort_dir' => $sort_dir,
            'sort' => $sort,
            'qs' => $qs,
        ]);
    }

    /**
     * Shows detailed information about a comp, including stats and teams.
     *
     * @param int $id
     */
    public function getComp($id)
    {
        $comp = Comp::find($id);
        if (!$comp || $comp->getBracket()->id != $this->om->bracket->id) {
            return redirect()->route('index');
        }
        $q = Performance::where('bracket_id', '=', $this->om->bracket->id)
            ->where('season_id', '=', $this->om->season->id)
            ->where('comp_id', '=', $comp->id);
        if ($this->om->region) {
            $q->where('region_id', '=', $this->om->region->id);
        }
        else {
            $q->whereNull('region_id');
        }
        if ($this->om->term) {
            $q->where('term_id', '=', $this->om->term->id);
        }
        else {
            $q->whereNull('term_id');
        }
        $performance = $q->first();
        return view('comp', [
            'comp' => $comp,
            'specs' => $comp->getSpecs(),
            'performance' => $performance,
            'teams' => $comp->getTeamsBuilder($this->om)->paginate(20)
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
        OptionsManager::setBracket($request->input('bracket'));
        OptionsManager::setRegion($request->input('region'));
        OptionsManager::setSeason($request->input('season'));
        OptionsManager::setTerm($request->input('term'));
        return back();
    }
}
