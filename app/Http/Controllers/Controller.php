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
        View::share('xtime', microtime(true));
        $this->middleware(function ($request, $next) {
            OptionsManager::setSeason($request->input('season'));
            OptionsManager::setBracket($request->input('bracket'));
            OptionsManager::setRegion($request->input('region'));
            OptionsManager::setTerm($request->input('term'));
            $om = OptionsManager::build();
            $this->om = $om;
            View::share('om', $om);
            $params = array_merge([
                'region' => $om->region ? $om->region->name : 'all',
                'season' => $om->season->id,
                'bracket_id' => $om->bracket->name,
                'term' => $om->term ? $om->term->id : 'all',
            ], $request->all());
            View::share('share_url', url()->current() . '?' . http_build_query($params));
            return $next($request);
        });
    }
}
