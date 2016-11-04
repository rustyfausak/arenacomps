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

    public function group()
    {
        return $this->belongsTo('App\Models\Group', 'group_id', 'id');
    }

    public function spec()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id', 'id');
    }

    public function team()
    {
        return $this->belongsTo('App\Models\Team', 'team_id', 'id');
    }

    public function comp()
    {
        return $this->belongsTo('App\Models\Comp', 'comp_id', 'id');
    }
}
