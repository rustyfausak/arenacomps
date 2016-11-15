<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\OptionsManager;

class Rep extends Model
{
    public $timestamps = true;
    protected $table = 'reps';
    protected $guarded = [];

    public function race()
    {
        return $this->belongsTo('App\Models\Race', 'race_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }

    public function spec()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id', 'id');
    }

    public function bracket()
    {
        return $this->belongsTo('App\Models\Bracket', 'bracket_id', 'id');
    }

    public function season()
    {
        return $this->belongsTo('App\Models\Bracket', 'season_id', 'id');
    }

    public function region()
    {
        return $this->belongsTo('App\Models\Bracket', 'region_id', 'id');
    }

    public function term()
    {
        return $this->belongsTo('App\Models\Bracket', 'term_id', 'id');
    }

    /**
     * @param OptionsManager $om
     * @param array $params
     * @param int $num
     */
    public static function collapse(OptionsManager $om, array $params = [], $num = 0)
    {
        $date = date("Y-m-d");
        $regions = [null, $om->region];
        foreach ($regions as $region) {
            $q = Rep::where('for_date', '=', $date)
                ->where('bracket_id', '=', $om->bracket->id);
            if ($region) {
                $q->where('region_id', '=', $region->id);
            }
            else {
                $q->whereNull('region_id');
            }
            foreach ([
                'role_id',
                'spec_id',
                'race_id'
            ] as $k) {
                if (array_key_exists($k, $params)) {
                    $q->where($k, '=', $params[$k]);
                }
                else {
                    $q->whereNull($k);
                }
            }
            $rep = $q->first();
            if (!$rep) {
                $params = array_merge($params, [
                    'for_date' => $date,
                    'bracket_id' => $om->bracket->id,
                    'num' => $num,
                    'num_leaderboards' => 1,
                ]);
                if ($region) {
                    $params['region_id'] = $region->id;
                }
                $rep = Rep::create($params);
            }
            else {
                $rep->num = ($rep->num * $rep->num_leaderboards + $num) / ($rep->num_leaderboards + 1);
                $rep->num_leaderboards++;
                $rep->save();
            }
        }
    }
}
