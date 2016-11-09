<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;
    protected $table = 'roles';
    protected $guarded = [];

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
