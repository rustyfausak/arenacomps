<?php

namespace App\Models;

use DB;
use App\OptionsManager;
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
     * @return Bracket
     */
    public function getBracket()
    {
        return Bracket::where('size', '=', sizeof(array_filter([
                $this->spec_id1,
                $this->spec_id2,
                $this->spec_id3,
            ])))
            ->first();
    }

    /**
     * @return array of Spec
     */
    public function getSpecs()
    {
        return array_filter([$this->spec1, $this->spec2, $this->spec3]);
    }

    /**
     * @param OptionsManager $om
     * @return array
     */
    public function getTeamsBuilder(OptionsManager $om)
    {
        $q = DB::table('snapshots AS s')
            ->select('s.team_id')
            ->leftJoin('groups AS g', 'g.id', '=', 's.group_id')
            ->leftJoin('leaderboards AS l', 'g.leaderboard_id', '=', 'l.id')
            ->where('s.comp_id', '=', $this->id)
            ->where('l.season_id', '=', $om->season->id);
        if ($om->region) {
            $q->where('l.region_id', '=', $om->region->id);
        }
        if ($om->term) {
            $q->where('term_id', '=', $om->term->id);
        }
        $team_ids = $q->groupBy('s.team_id')
            ->pluck('team_id');
        return Team::whereIn('id', $team_ids);
    }

    /**
     * @param OptionsManager $om
     * @return int
     */
    public function numTeams(OptionsManager $om)
    {
        $q = DB::table('snapshots AS s')
            ->leftJoin('groups AS g', 's.group_id', '=', 'g.id')
            ->leftJoin('leaderboards AS l', 'g.leaderboard_id', '=', 'l.id')
            ->where('s.comp_id', '=', $this->id)
            ->where('l.season_id', '=', $om->season->id)
            ->where('l.bracket_id', '=', $om->bracket->id);
        if ($om->region) {
            $q->where('l.region_id', '=', $om->region->id);
        }
        if ($om->term) {
            $q->where('l.term_id', '=', $om->term->id);
        }
        $results = $q->groupBy('s.team_id')
            ->pluck('s.team_id');
        return sizeof($results);
    }

    /**
     * @param OptionsManager $om
     * @return Performance
     */
    public function getPerformance(OptionsManager $om)
    {
        $om->comp = $this;
        return Performance::build($om);
    }
}
