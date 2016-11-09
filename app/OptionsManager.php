<?php

namespace App;

use Session;
use App\Models\Bracket;
use App\Models\Region;
use App\Models\Season;
use App\Models\Term;

class OptionsManager
{
    /**
     * @var Season
     */
    public $season;

    /**
     * @var Bracket
     */
    public $bracket;

    /**
     * @var Region
     */
    public $region;

    /**
     * @var array of Region
     */
    public $regions;

    /**
     * @var Term
     */
    public $term;

    /**
     * @var array of Term
     */
    public $terms;

    /**
     * @param Season $season
     * @param Bracket $bracket
     * @param Region $region
     * @param Term $term
     */
    public function __construct(Season $season, Bracket $bracket, Region $region = null, Term $term = null)
    {
        $this->season = $season;
        $this->bracket = $bracket;
        $this->region = $region;
        $this->term = $term;

        if ($this->region) {
            $this->regions = [$this->region];
        }
        else {
            $this->regions = Region::all()->toArray();
        }
        if ($this->term) {
            $this->terms = [$this->term];
        }
        else {
            $this->terms = Term::all()->toArray();
        }
    }

    /**
     * @return OptionsManager
     */
    public static function build()
    {
        return new self(
            self::getSeason(),
            self::getBracket(),
            self::getRegion(),
            self::getTerm()
        );
    }

    // -------
    // bracket
    // -------

    /**
     * @param string $bracket_name
     */
    public static function setBracket($bracket_name = null)
    {
        if ($bracket_name) {
            $bracket = Bracket::where('name', '=', $bracket_name)->first();
            if ($bracket) {
                Session::put('bracket_id', $bracket->id);
            }
        }
        if (!Session::get('bracket_id')) {
            $bracket = Bracket::getDefault();
            Session::put('bracket_id', $bracket->id);
        }
    }

    /**
     * @return Bracket
     */
    public static function getBracket()
    {
        self::setBracket();
        return Bracket::find(Session::get('bracket_id'));
    }

    // ------
    // season
    // ------

    /**
     * @param int $season_id
     */
    public static function setSeason($season_id = null)
    {
        if ($season_id) {
            $season = Season::find($season_id);
            if ($season) {
                Session::put('season_id', $season->id);
            }
        }
        if (!Session::get('season_id')) {
            $season = Season::getDefault();
            Session::put('season_id', $season->id);
        }
    }

    /**
     * @return Season
     */
    public static function getSeason()
    {
        self::setSeason();
        return Season::find(Session::get('season_id'));
    }

    // ------
    // region
    // ------

    /**
     * @param string $region_name Or 'all'
     */
    public static function setRegion($region_name = null)
    {
        if ($region_name) {
            if ($region_name == 'all') {
                Session::put('region_id', 'all');
            }
            else {
                $region = Region::where('name', '=', $region_name)->first();
                if ($region) {
                    Session::put('region_id', $region->id);
                }
            }
        }
        if (!Session::get('region_id')) {
            Session::put('region_id', 'all');
        }
    }

    /**
     * @return Region|null
     */
    public static function getRegion()
    {
        self::setRegion();
        if (Session::get('region_id') == 'all') {
            return null;
        }
        return Region::find(Session::get('region_id'));
    }

    // ----
    // term
    // ----

    /**
     * @param int|'all' $term_id
     */
    public static function setTerm($term_id = null)
    {
        $season = self::getSeason();
        if ($term_id) {
            if ($term_id == 'all') {
                Session::put('term_id', 'all');
            }
            else {
                $term = Term::find($term_id);
                if ($term && $term->season_id == $season->id) {
                    Session::put('term_id', $term->id);
                }
            }
        }
        $term = Term::find(Session::get('term_id'));
        if (!$term || $term->season_id != $season->id) {
            Session::put('term_id', 'all');
        }
        if (!Session::get('term_id')) {
            Session::put('term_id', 'all');
        }
    }

    /**
     * @return Term|null
     */
    public static function getTerm()
    {
        self::setTerm();
        if (Session::get('term_id') == 'all') {
            return null;
        }
        return Term::find(Session::get('term_id'));
    }
}
