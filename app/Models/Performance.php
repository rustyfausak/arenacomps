<?php

namespace App\Models;

use DB;
use App\OptionsManager;
use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    public $timestamps = true;
    protected $table = 'performance';
    protected $guarded = [];

    const MAX_AGE_SECONDS = 60 * 60;

    public function bracket()
    {
        return $this->belongsTo('App\Models\Bracket', 'bracket_id', 'id');
    }

    public function season()
    {
        return $this->belongsTo('App\Models\Season', 'season_id', 'id');
    }

    public function term()
    {
        return $this->belongsTo('App\Models\Term', 'term_id', 'id');
    }

    public function comp()
    {
        return $this->belongsTo('App\Models\Comp', 'comp_id', 'id');
    }

    public function team()
    {
        return $this->belongsTo('App\Models\Team', 'team_id', 'id');
    }

    /**
     * @param int $wins
     * @param int $losses
     * @return float
     */
    public static function getSkill($wins, $losses, $avg_rating)
    {
        $ratio = $wins;
        if ($losses) {
            $ratio = round($wins / $losses, 2);
        }
        return max(0, min(20, $ratio)) * min(1, round(($wins + $losses) / 20, 2));
    }

    /**
     * @param OptionsManager $om
     * @return Performance
     */
    public static function build(OptionsManager $om) {
        if (!$om->team && !$om->comp) {
            return null;
        }
        $q = self::where('bracket_id', '=', $om->bracket->id)
            ->where('season_id', '=', $om->season->id);
        if ($om->region) {
            $q->where('region_id', '=', $om->region->id);
        }
        else {
            $q->whereNull('region_id');
        }
        if ($om->team) {
            $q->where('team_id', '=', $om->team->id);
        }
        else {
            $q->whereNull('team_id');
        }
        if ($om->comp) {
            $q->where('comp_id', '=', $om->comp->id);
        }
        else {
            $q->whereNull('comp_id');
        }
        if ($om->term) {
            $q->where('term_id', '=', $om->term->id);
        }
        else {
            $q->whereNull('term_id');
        }
        $performance = $q->first();
        if ($performance) {
            if (strtotime($performance->updated_at) >= time() - self::MAX_AGE_SECONDS) {
                return $performance;
            }
        }
        else {
            $performance = self::create([
                'bracket_id' => $om->bracket->id,
                'season_id' => $om->season->id,
                'region_id' => $om->region ? $om->region->id : null,
                'team_id' => $om->team ? $om->team->id : null,
                'comp_id' => $om->comp ? $om->comp->id : null,
                'term_id' => $om->term ? $om->term->id : null,
            ]);
        }

        // Generate performance data
        $data = [
            'num_snapshots' => 0,
            'total_rating' => 0,
            'avg_rating' => 0,
            'wins' => 0,
            'losses' => 0,
        ];
        $q = DB::table('snapshots AS s')
            ->select([
                DB::raw('AVG(s.rating) AS avg_rating'),
                DB::raw('SUM(g.wins) AS wins'),
                DB::raw('SUM(g.losses) AS losses'),
            ])
            ->leftJoin('groups AS g', 's.group_id', '=', 'g.id')
            ->leftJoin('leaderboards AS l', 'g.leaderboard_id', '=', 'l.id')
            ->where('l.bracket_id', '=', $om->bracket->id)
            ->where('l.season_id', '=', $om->season->id);
        if ($om->region) {
            $q->where('l.region_id', '=', $om->region->id);
        }
        if ($om->term) {
            $q->where('l.term_id', '=', $om->term->id);
        }
        if ($om->team) {
            $q->where('s.team_id', '=', $om->team->id);
        }
        if ($om->comp) {
            $q->where('s.comp_id', '=', $om->comp->id);
        }
        $results = $q->groupBy('s.team_id')
            ->get();
        foreach ($results as $result) {
            $data['num_snapshots']++;
            $data['total_rating'] += $result->avg_rating;
            $data['wins'] += $result->wins;
            $data['losses'] += $result->losses;
        }
        if ($data['num_snapshots']) {
            $data['avg_rating'] = round($data['total_rating'] / $data['num_snapshots']);
        }
        $performance->wins = floor($data['wins'] / $om->bracket->size);
        $performance->losses = floor($data['losses'] / $om->bracket->size);
        $performance->avg_rating = $data['avg_rating'];
        $performance->skill = self::getSkill($data['wins'], $data['losses'], $data['avg_rating']);
        if ($om->comp && !$om->team) {
            $performance->num_teams = $om->comp->numTeams($om);
        }
        $performance->save();
        return $performance;
    }
}
