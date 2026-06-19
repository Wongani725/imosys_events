<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        "/xml/tnm",
        "/download/apk",
    ];

    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if(last(explode('\\', get_class($response))) != 'RedirectResponse') {
            $response->headers->set('P3P', 'CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT');
        }

        return $response;
    }
}
