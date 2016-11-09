<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    public $timestamps = false;
    protected $table = 'genders';
    protected $guarded = [];

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
