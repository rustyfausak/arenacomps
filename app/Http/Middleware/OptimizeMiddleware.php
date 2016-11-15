<?php

namespace App\Http\Middleware;

use App;
use Closure;

class OptimizeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (!App::environment('local')) {
            $buffer = $response->getContent();
            $search = array(
                '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
                '/[^\S ]+\</s',  // strip whitespaces before tags, except space
                '/(\s)+/s'       // shorten multiple whitespace sequences
            );
            $replace = array(
                '>',
                '<',
                '\\1'
            );
            $buffer = preg_replace($search, $replace, $buffer);
            $response->setContent($buffer);
            ini_set('zlib.output_compression', 'On'); // If you like to enable GZip, too!
        }
        return $response;
    }
}
