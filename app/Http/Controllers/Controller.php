<?php

namespace App\Http\Controllers;

use Session;
use App\OptionsManager;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            OptionsManager::setSeason($request->input('season'));
            OptionsManager::setBracket($request->input('bracket'));
            OptionsManager::setRegion($request->input('region'));
            OptionsManager::setTerm($request->input('term'));
            $bracket = OptionsManager::getBracket();
            $season = OptionsManager::getSeason();
            $term = OptionsManager::getTerm();
            $region = OptionsManager::getRegion();
            View::share('bracket', $bracket);
            View::share('season', $season);
            View::share('term', $term);
            View::share('region', $region);
            $params = array_merge([
                'region' => $region ? $region->name : 'all',
                'season' => $season->id,
                'bracket_id' => $bracket->name,
                'term' => $term ? $term->id : 'all',
            ], $request->all());
            View::share('share_url', url()->current() . '?' . http_build_query($params));
            return $next($request);
        });
    }
}
