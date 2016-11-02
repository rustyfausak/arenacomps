<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spec extends Model
{
    public $timestamps = false;
    protected $table = 'specs';
    protected $guarded = [];

    public function role()
    {
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }

    public function specType()
    {
        return $this->belongsTo('App\Models\SpecType', 'spec_type_id', 'id');
    }
}
