<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    public $timestamps = true;
    protected $table = 'leaderboards';
    protected $guarded = [];
}
