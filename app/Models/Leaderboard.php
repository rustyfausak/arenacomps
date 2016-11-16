<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    public $timestamps = true;
    protected $table = 'leaderboards';
    protected $guarded = [];

    public function region()
    {
        return $this->belongsTo('App\Models\Region', 'region_id', 'id');
    }

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

    /**
     * Returns the previous leaderboard.
     *
     * @return Leaderboard
     */
    public function getPrevious()
    {
        return self::where('region_id', '=', $this->region_id)
            ->where('bracket_id', '=', $this->bracket_id)
            ->where('season_id', '=', $this->season_id)
            ->where('term_id', '=', $this->term_id)
            ->where('id', '<', $this->id)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->first();
    }

    /**
     * Returns the next leaderboard.
     *
     * @return Leaderboard
     */
    public function getNext()
    {
        return self::where('region_id', '=', $this->region_id)
            ->where('bracket_id', '=', $this->bracket_id)
            ->where('season_id', '=', $this->season_id)
            ->where('term_id', '=', $this->term_id)
            ->where('id', '>', $this->id)
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->first();
    }
}
