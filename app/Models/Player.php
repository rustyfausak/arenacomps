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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @param Bracket $bracket
     * @return Collection of Team
     */
    public function getTeams(Bracket $bracket)
    {
        $q = Team::whereRaw(1);
        $cols = [];
        for ($i = 1; $i <= 5; $i++) {
            $key = 'player_id' . $i;
            if ($i > $bracket->size) {
                $q->whereNull($key);
            }
            else {
                $q->whereNotNull($key);
                $cols[] = $key;
            }
        }
        $player_id = $this->id;
        $q->where(function ($q) use ($cols, $player_id) {
            $q->whereRaw(0);
            foreach ($cols as $col) {
                $q->orWhere($col, '=', $player_id);
            }
        });
        return $q->get();
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
