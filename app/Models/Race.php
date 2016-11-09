<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    public $timestamps = false;
    protected $table = 'races';
    protected $guarded = [];

    public function faction()
    {
        return $this->belongsTo('App\Models\Faction', 'faction_id', 'id');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
