<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    public $timestamps = false;
    protected $table = 'regions';
    protected $guarded = [];

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
