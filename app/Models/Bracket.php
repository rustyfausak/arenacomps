<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bracket extends Model
{
    public $timestamps = false;
    protected $table = 'brackets';
    protected $guarded = [];

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Bracket
     */
    public static function getDefault()
    {
        return self::where('name', '=', '3v3')->first();
    }
}
