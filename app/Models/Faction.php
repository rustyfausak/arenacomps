<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faction extends Model
{
    public $timestamps = false;
    protected $table = 'factions';
    protected $guarded = [];
}
