<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
    public $timestamps = false;
    protected $table = 'snapshots';
    protected $guarded = [];

    public function player()
    {
        return $this->belongsTo('App\Models\Player', 'player_id', 'id');
    }

    public function leaderboard()
    {
        return $this->belongsTo('App\Models\Leaderboard', 'leaderboard_id', 'id');
    }

    public function spec()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id', 'id');
    }
}
