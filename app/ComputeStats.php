<?php

namespace App;

class ComputeStats
{
    public $num;
    public $pct;
    public $breakdown;
    public $role_id;
    public $spec_id;
    public $race_id;

    public function __construct($role_id = null, $spec_id = null, $race_id = null, $num = 0)
    {
        $this->num = $num;
        $this->pct = 0;
        $this->breakdown = [];
        $this->role_id = $role_id;
        $this->spec_id = $spec_id;
        $this->race_id = $race_id;
    }

    /**
     * @param Builder $q
     */
    public static function build($q)
    {
        $data = [
            'by_role' => [],
            'by_spec' => [],
            'by_race' => [],
        ];
        $base = [
            'num' => 0,
        ];
        $base_by_role = array_merge($base, [
            'by_race' => [],
            'by_spec' => [],
        ]);
        $base_by_spec = array_merge($base, [
            'by_race' => [],
        ]);
        $base_by_race = array_merge($base, [
            'by_role' => [],
        ]);
        $q->select([
            'role_id',
            'spec_id',
            'race_id',
            'num',
        ]);
        foreach ($q->get() as $rep) {
            if ($rep->role_id && !$rep->spec_id && !$rep->race_id) {
                if (!array_key_exists($rep->role_id, $data['by_role'])) {
                    $data['by_role'][$rep->role_id] = $base_by_role;
                }
                $data['by_role'][$rep->role_id]['num'] += $rep->num;
            }
            if ($rep->role_id && $rep->spec_id && !$rep->race_id) {
                if (!array_key_exists($rep->spec_id, $data['by_spec'])) {
                    $data['by_spec'][$rep->spec_id] = $base_by_spec;
                }
                if (!array_key_exists($rep->spec_id, $data['by_role'][$rep->role_id]['by_spec'])) {
                    $data['by_role'][$rep->role_id]['by_spec'][$rep->spec_id] = $base;
                }
                $data['by_spec'][$rep->spec_id]['num'] += $rep->num;
                $data['by_role'][$rep->role_id]['by_spec'][$rep->spec_id]['num'] += $rep->num;
            }
            if (!$rep->role_id && !$rep->spec_id && $rep->race_id) {
                if (!array_key_exists($rep->race_id, $data['by_race'])) {
                    $data['by_race'][$rep->race_id] = $base_by_race;
                }
                $data['by_race'][$rep->race_id]['num'] += $rep->num;
            }
            if ($rep->role_id && !$rep->spec_id && $rep->race_id) {
                if (!array_key_exists($rep->role_id, $data['by_role'])) {
                    $data['by_role'][$rep->role_id] = $base_by_role;
                }
                if (!array_key_exists($rep->race_id, $data['by_race'])) {
                    $data['by_race'][$rep->race_id] = $base_by_race;
                }
                if (!array_key_exists($rep->race_id, $data['by_role'][$rep->role_id]['by_race'])) {
                    $data['by_role'][$rep->role_id]['by_race'][$rep->race_id] = $base;
                }
                if (!array_key_exists($rep->role_id, $data['by_race'][$rep->race_id]['by_role'])) {
                    $data['by_race'][$rep->race_id]['by_role'][$rep->role_id] = $base;
                }
                $data['by_role'][$rep->role_id]['by_race'][$rep->race_id]['num'] += $rep->num;
                $data['by_race'][$rep->race_id]['by_role'][$rep->role_id]['num'] += $rep->num;
            }
            if ($rep->role_id && $rep->spec_id && $rep->race_id) {
                if (!array_key_exists($rep->spec_id, $data['by_spec'])) {
                    $data['by_spec'][$rep->spec_id] = $base_by_spec;
                }
                if (!array_key_exists($rep->race_id, $data['by_spec'][$rep->spec_id]['by_race'])) {
                    $data['by_spec'][$rep->spec_id]['by_race'][$rep->race_id] = $base;
                }
                $data['by_spec'][$rep->spec_id]['by_race'][$rep->race_id]['num'] += $rep->num;
            }
        }
        self::computePct($data['by_role']);
        self::computePct($data['by_spec']);
        self::computePct($data['by_race']);
        return $data;
    }

    public static function computePct(&$data)
    {
        $total = 0;
        $sort = [];
        foreach ($data as $id => $arr) {
            $data[$id]['id'] = $id;
            $sort[$id] = $arr['num'];
            $total += $arr['num'];
            foreach ($arr as $k => $v) {
                if (preg_match('/by/', $k)) {
                    self::computePct($data[$id][$k]);
                }
            }
        }
        $keys = array_keys($data);
        array_multisort($sort, SORT_DESC, $data, $keys);
        $data = array_combine($keys, $data);
        foreach ($data as $id => $arr) {
            $pct = $total ? round($arr['num'] / $total * 100, 2) : 0;
            $data[$id]['pct'] = $pct;
        }
    }
}
