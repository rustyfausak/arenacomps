<?php

namespace App\Traits;

trait DateRangeable
{
    /**
     * @return object of self or null
     */
    public static function getActive()
    {
        return self::where('start_date', '<=', date("Y-m-d"))
            ->where(function ($q) {
                $q->where('end_date', '>=', date("Y-m-d"))
                    ->orWhereNull('end_date');
            })
            ->orderBy('start_date', 'DESC')
            ->first();
    }

    /**
     * @return object of self
     */
    public static function getDefault()
    {
        $obj = self::getActive();
        if ($obj) {
            return $obj;
        }
        return self::orderBy('start_date', 'DESC')
            ->first();
    }
}
