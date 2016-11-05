<?php

namespace App\Models;

use App\Traits\ColumnSequence;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use ColumnSequence;

    public $timestamps = false;
    protected $table = 'teams';
    protected $guarded = [];

    protected static $col_seq_prefix = 'player_id';

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
     * @return Collection of Comp
     */
    public function getComps()
    {
        $comp_ids = Snapshot::where('team_id', '=', $this->id)
            ->groupBy('comp_id')
            ->pluck('comp_id')
            ->toArray();
        return Comp::whereIn('id', $comp_ids)->get();
    }

    /**
     * @param Season  $season
     * @param Comp    $comp
     * @param Term    $term
     * @return array
     */
    public function getPerformance(Season $season, Comp $comp = null, Term $term = null)
    {
        $bracket = Bracket::where('size', '=', $this->getSize())->first();
        return Performance::build($bracket, $season, $this, $comp, $term);
    }
}
