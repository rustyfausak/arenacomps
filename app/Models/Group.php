<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public $timestamps = false;
    protected $table = 'groups';
    protected $guarded = [];

    public function leaderboard()
    {
        return $this->belongsTo('App\Models\Leaderboard', 'leaderboard_id', 'id');
    }

    public function faction()
    {
        return $this->belongsTo('App\Models\Faction', 'faction_id', 'id');
    }
}
