<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Realm extends Model
{
    public $timestamps = false;
    protected $table = 'realms';
    protected $guarded = [];

    public function region()
    {
        return $this->belongsTo('App\Models\Region', 'region_id', 'id');
    }

    public function __toString()
    {
        return $this->name;
    }
}
