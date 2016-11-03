<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    public $timestamps = false;
    protected $table = 'stats';
    protected $guarded = [];

    public function player()
    {
        return $this->belongsTo('App\Models\Player', 'player_id', 'id');
    }

    public function bracket()
    {
        return $this->belongsTo('App\Models\Bracket', 'bracket_id', 'id');
    }

    public function leaderboard()
    {
        return $this->belongsTo('App\Models\Leaderboard', 'leaderboard_id', 'id');
    }
}
