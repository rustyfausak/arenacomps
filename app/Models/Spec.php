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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @param array of Spec $specs
     * @return array
     */
    public static function sort(array $specs = [])
    {
        $sort1 = [];
        $sort2 = [];
        foreach ($specs as $k => $spec) {
            $sort1[$k] = $spec->spec_type_id;
            $sort2[$k] = $spec->name;
        }
        array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $specs);
        return $specs;
    }
}
