<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    public $timestamps = false;
    protected $table = 'teams';
    protected $guarded = [];

    protected $_perf = null;

    public function player1()
    {
        return $this->belongsTo('App\Models\Player', 'player_id1', 'id');
    }

    public function player2()
    {
        return $this->belongsTo('App\Models\Player', 'player_id2', 'id');
    }

    public function player3()
    {
        return $this->belongsTo('App\Models\Player', 'player_id3', 'id');
    }

    public function player4()
    {
        return $this->belongsTo('App\Models\Player', 'player_id4', 'id');
    }

    public function player5()
    {
        return $this->belongsTo('App\Models\Player', 'player_id5', 'id');
    }

    /**
     * @return array
     */
    public function getPerf()
    {
        if (!$this->_perf) {
            return $this->generatePerf();
        }
        return $this->_perf;
    }

    public function generatePerf()
    {
        $this->_perf = [
            'num_snapshots' => 0,
            'total_rating' => 0,
            'avg_rating' => 0,
            'wins' => 0,
            'losses' => 0,
            'by_comp' => [],
        ];
        $snapshots = Snapshot::where('team_id', '=', $this->id)
            ->get();
        $size = 1;
        foreach ($snapshots as $snapshot) {
            $size = $snapshot->leaderboard->bracket->size;
            $this->_perf['num_snapshots']++;
            $this->_perf['total_rating'] += $snapshot->rating;
            $this->_perf['wins'] += $snapshot->wins;
            $this->_perf['losses'] += $snapshot->losses;
            if (!array_key_exists($snapshot->comp_id, $this->_perf['by_comp'])) {
                $this->_perf['by_comp'][$snapshot->comp_id] = [
                    'num_snapshots' => 0,
                    'total_rating' => 0,
                    'avg_rating' => 0,
                    'wins' => 0,
                    'losses' => 0,
                ];
            }
            $this->_perf['by_comp'][$snapshot->comp_id]['num_snapshots']++;
            $this->_perf['by_comp'][$snapshot->comp_id]['total_rating'] += $snapshot->rating;
            $this->_perf['by_comp'][$snapshot->comp_id]['wins'] += $snapshot->wins;
            $this->_perf['by_comp'][$snapshot->comp_id]['losses'] += $snapshot->losses;
        }
        foreach ($this->_perf['by_comp'] as $comp_id => $comp_perf) {
            if ($comp_perf['num_snapshots']) {
                $comp_perf['avg_rating'] = round($comp_perf['total_rating'] / $comp_perf['num_snapshots']);
            }
            $comp_perf['wins'] /= $size;
            $comp_perf['losses'] /= $size;
            $this->_perf['by_comp'][$comp_id] = $comp_perf;
        }
        if ($this->_perf['num_snapshots']) {
            $this->_perf['avg_rating'] = round($this->_perf['total_rating'] / $this->_perf['num_snapshots']);
        }
        $this->_perf['wins'] /= $size;
        $this->_perf['losses'] /= $size;
        return $this->_perf;
    }

    /**
     * @param array $player_ids
     */
    public static function getOrBuild($player_ids)
    {
        $q = self::whereRaw(1);
        $params = [];
        for ($i = 0; $i < sizeof($player_ids); $i++) {
            $col = 'player_id' . ($i + 1);
            $val = $player_ids[$i];
            $q->where($col, '=', $val);
            $params[$col] = $val;
        }
        $obj = $q->first();
        if (!$obj) {
            $obj = self::create($params);
        }
        return $obj;
    }
}
