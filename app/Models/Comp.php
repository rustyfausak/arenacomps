<?php

namespace App\Models;

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

    public function spec4()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id4', 'id');
    }

    public function spec5()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id5', 'id');
    }

    /**
     * @return array of Spec
     */
    public function getSpecs()
    {
        return array_filter([$this->spec1, $this->spec2, $this->spec3, $this->spec4, $this->spec5]);
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
