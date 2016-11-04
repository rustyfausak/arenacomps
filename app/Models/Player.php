<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public $timestamps = false;
    protected $table = 'players';
    protected $guarded = [];

    public function realm()
    {
        return $this->belongsTo('App\Models\Realm', 'realm_id', 'id');
    }

    public function faction()
    {
        return $this->belongsTo('App\Models\Faction', 'faction_id', 'id');
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

    public function stats()
    {
        return $this->hasMany('App\Models\Stat', 'player_id', 'id');
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection
     */
    public function getTeams()
    {
        return Team::where('player_id1', '=', $this->id)
            ->orWhere('player_id2', '=', $this->id)
            ->orWhere('player_id3', '=', $this->id)
            ->get();
    }

    /**
     * Get the UID for the player.
     *
     * @param string $name
     * @return string
     */
    public static function getUid($name)
    {
        return md5($name);
    }
}
