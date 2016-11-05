<?php

namespace App\Models;

use App\Traits\DateRangeable;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use DateRangeable;

    public $timestamps = false;
    protected $table = 'terms';
    protected $guarded = [];
}
