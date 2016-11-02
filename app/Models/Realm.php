<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Realm extends Model
{
    public $timestamps = false;
    protected $table = 'realms';
    protected $guarded = [];
}
