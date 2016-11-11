<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rep extends Model
{
    public $timestamps = true;
    protected $table = 'reps';
    protected $guarded = [];

    public function race()
    {
        return $this->belongsTo('App\Models\Race', 'race_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }

    public function spec()
    {
        return $this->belongsTo('App\Models\Spec', 'spec_id', 'id');
    }
}
