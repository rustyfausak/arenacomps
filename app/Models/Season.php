<?php

namespace App\Models;

use App\Traits\DateRangeable;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use DateRangeable;

    public $timestamps = false;
    protected $table = 'seasons';
    protected $guarded = [];

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
