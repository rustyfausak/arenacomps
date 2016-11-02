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

    public function __toString()
    {
        return $this->name;
    }
}
