<?php

namespace App\Models;

use App\OptionsManager;
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

    /**
     * @return array of Player
     */
    public function getPlayers()
    {
        return array_filter([$this->player1, $this->player2, $this->player3]);
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
     * @param OptionsManager $om
     * @return Performance
     */
    public function getPerformance(OptionsManager $om)
    {
        $om->team = $this;
        return Performance::build($om);
    }
}
