<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delta extends Model
{
    public $timestamps = false;
    protected $table = 'deltas';
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

    public function gender()
    {
        return $this->belongsTo('App\Models\Gender', 'gender_id', 'id');
    }
}
