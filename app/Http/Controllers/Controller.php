<?php

namespace App\Http\Controllers;

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
            OptionsController::setBracket($request->input('bracket'));
            OptionsController::setRegion($request->input('region'));
            OptionsController::setSeason($request->input('season'));
            OptionsController::setTerm($request->input('term'));
            View::share('bracket', OptionsController::getBracket());
            View::share('season', OptionsController::getSeason());
            View::share('term', OptionsController::getTerm());
            View::share('region', OptionsController::getRegion());
            return $next($request);
        });
    }
}
