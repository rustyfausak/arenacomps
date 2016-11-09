<?php

namespace App\Models;

use DB;
use App\Traits\ColumnSequence;
use Illuminate\Database\Eloquent\Model;

class Comp extends Model
{
    use ColumnSequence;

    public $timestamps = false;
    protected $table = 'comps';
    protected $guarded = [];

    protected static $col_seq_prefix = 'spec_id';

    public function spec1()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id1', 'id');
    }

    public function spec2()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id2', 'id');
    }

    public function spec3()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id3', 'id');
    }

    /**
     * @return array of Spec
     */
    public function getSpecs()
    {
        return array_filter([$this->spec1, $this->spec2, $this->spec3]);
    }

    /**
     * @param Season  $season
     * @param Region  $region
     * @param Term    $term
     * @param bool    $return_builder
     * @return array
     */
    public function getTeams(Season $season, Region $region = null, Term $term = null, $return_builder = false)
    {
        $q = DB::table('snapshots AS s')
            ->select('s.team_id')
            ->leftJoin('groups AS g', 'g.id', '=', 's.group_id')
            ->leftJoin('leaderboards AS l', 'g.leaderboard_id', '=', 'l.id')
            ->where('s.comp_id', '=', $this->id)
            ->where('l.season_id', '=', $season->id);
        if ($region) {
            $q->where('l.region_id', '=', $region->id);
        }
        if ($term) {
            $q->where('term_id', '=', $term->id);
        }
        $team_ids = $q->groupBy('s.team_id')->pluck('team_id');
        $q = Team::whereIn('id', $team_ids);
        if ($return_builder) {
            return $q;
        }
        return $q->get();
    }

    /**
     * @return int
     */
    public function numTeams()
    {
        $q = DB::table('snapshots')
            ->where('comp_id', '=', $this->id)
            ->groupBy('team_id')
            ->pluck('team_id');
        return sizeof($q);
    }

    /**
     * @param Season  $season
     * @param Region  $region
     * @param Team    $team
     * @param Term    $term
     * @return array
     */
    public function getPerformance(Season $season, Region $region = null, Team $team = null, Term $term = null)
    {
        $bracket = Bracket::where('size', '=', $this->getSize())->first();
        return Performance::build($bracket, $season, $region, $team, $this, $term);
    }
}
