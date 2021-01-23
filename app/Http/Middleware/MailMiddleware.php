<?php

namespace App\Http\Middleware;
use App;
use Closure;
class MailMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $app = \App::getInstance();
        $app->register('\App\Providers\CustomMailServiceProvider');
        return $next($request);
    }
}