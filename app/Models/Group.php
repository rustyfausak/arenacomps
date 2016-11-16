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

    public function snapshots()
    {
        return $this->hasMany('App\Models\Snapshots', 'snapshot_id', 'id');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "group #{$this->id}:\tLB #{$this->leaderboard_id}\tFAC #{$this->faction_id}\t({$this->wins}-{$this->losses})";
    }
}
