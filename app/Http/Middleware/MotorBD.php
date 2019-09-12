<?php

namespace App\Http\Middleware;

use Closure;
use Config;

class MotorBD
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
        
        $motorbd = session('motorbd', 'mysql');
        
        if($motorbd) {
            config()->set('database.default', $motorbd);   
        }
        
        return $next($request);
    }
}
