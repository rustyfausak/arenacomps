<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecType extends Model
{
    public $timestamps = false;
    protected $table = 'spec_types';
    protected $guarded = [];

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
