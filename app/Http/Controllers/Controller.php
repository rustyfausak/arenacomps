<?php

namespace App\Http\Controllers;

use Session;
use App\Http\Controllers\OptionsController;
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
            OptionsController::setSeason($request->input('season'));
            OptionsController::setBracket($request->input('bracket'));
            OptionsController::setRegion($request->input('region'));
            OptionsController::setTerm($request->input('term'));
            $bracket = OptionsController::getBracket();
            $season = OptionsController::getSeason();
            $term = OptionsController::getTerm();
            $region = OptionsController::getRegion();
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
