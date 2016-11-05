<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    public $timestamps = true;
    protected $table = 'performance';
    protected $guarded = [];

    const MAX_AGE_SECONDS = 1;

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
    public static function getSkill($wins, $losses)
    {
        $ratio = $wins;
        if ($losses) {
            $ratio = round($wins / $losses, 2);
        }
        return max(0, min(10, $ratio)) * min(1, round(($wins + $losses) / 10, 2));
    }

    /**
     * @param Bracket $bracket
     * @param Season  $season
     * @param Team    $team
     * @param Comp    $comp
     * @param Term    $term
     * @return Performance
     */
    public static function build(
        Bracket $bracket,
        Season $season,
        Team $team = null,
        Comp $comp = null,
        Term $term = null
    ) {
        if (!$team && !$comp) {
            return null;
        }
        $q = self::where('bracket_id', '=', $bracket->id)
            ->where('season_id', '=', $season->id);
        if ($team) {
            $q->where('team_id', '=', $team->id);
        }
        if ($comp) {
            $q->where('comp_id', '=', $comp->id);
        }
        if ($term) {
            $q->where('term_id', '=', $term->id);
        }
        $performance = $q->first();
        if ($performance) {
            if (strtotime($performance->updated_at) >= time() - self::MAX_AGE_SECONDS) {
                return $performance;
            }
        }
        else {
            $performance = self::create([
                'bracket_id' => $bracket->id,
                'season_id' => $season->id,
                'team_id' => $team ? $team->id : null,
                'comp_id' => $comp ? $comp->id : null,
                'term_id' => $term ? $term->id : null,
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
                DB::raw('g.wins'),
                DB::raw('g.losses'),
            ])
            ->leftJoin('groups AS g', 's.group_id', '=', 'g.id')
            ->leftJoin('leaderboards AS l', 'g.leaderboard_id', '=', 'l.id')
            ->where('l.bracket_id', '=', $bracket->id)
            ->where('l.season_id', '=', $season->id);
        if ($term) {
            $q->where('l.term_id', '=', $term->id);
        }
        if ($team) {
            $q->where('s.team_id', '=', $team->id);
        }
        if ($comp) {
            $q->where('s.comp_id', '=', $comp->id);
        }
        $results = $q->groupBy('s.group_id', 's.team_id')
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
        $performance->wins = $data['wins'];
        $performance->losses = $data['losses'];
        $performance->avg_rating = $data['avg_rating'];
        $performance->skill = self::getSkill($data['wins'], $data['losses']);
        $performance->save();
        return $performance;
    }
}
