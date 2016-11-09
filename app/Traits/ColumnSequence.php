<?php

namespace App\Traits;

trait ColumnSequence
{
    /**
     * @return int
     */
    public function getSize()
    {
        $count = 0;
        for ($i = 1; $i <= 3; $i++) {
            if ($this->{static::$col_seq_prefix . $i}) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @param array of int $ids
     * @return self
     */
    public static function getOrBuild($ids)
    {
        sort($ids);
        $q = self::whereRaw(1);
        $params = [];
        for ($i = 0; $i < sizeof($ids); $i++) {
            $col = static::$col_seq_prefix . ($i + 1);
            $val = $ids[$i];
            $q->where($col, '=', $val);
            $params[$col] = $val;
        }
        $obj = $q->first();
        if (!$obj) {
            $obj = self::create($params);
        }
        return $obj;
    }
}
