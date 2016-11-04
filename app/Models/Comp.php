<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comp extends Model
{
    public $timestamps = false;
    protected $table = 'comps';
    protected $guarded = [];

    protected $_perf = null;

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

    public function spec4()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id4', 'id');
    }

    public function spec5()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id5', 'id');
    }

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
        $snapshots = Snapshot::where('comp_id', '=', $this->id)
            ->get();
        $size = 1;
        foreach ($snapshots as $snapshot) {
            $size = $snapshot->leaderboard->bracket->size;
            $this->_perf['num_snapshots']++;
            $this->_perf['total_rating'] += $snapshot->rating;
            $this->_perf['wins'] += $snapshot->wins;
            $this->_perf['losses'] += $snapshot->losses;
        }
        if ($this->_perf['num_snapshots']) {
            $this->_perf['avg_rating'] = round($this->_perf['total_rating'] / $this->_perf['num_snapshots']);
        }
        $this->_perf['wins'] /= $size;
        $this->_perf['losses'] /= $size;
        return $this->_perf;
    }

    /**
     * @param array $spec_ids
     */
    public static function getOrBuild($spec_ids)
    {
        $q = self::whereRaw(1);
        $params = [];
        for ($i = 0; $i < sizeof($spec_ids); $i++) {
            $col = 'spec_id' . ($i + 1);
            $val = $spec_ids[$i];
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
