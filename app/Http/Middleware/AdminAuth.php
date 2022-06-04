<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(!session()->has('user') && $request->path() != '/'
        && $request->path() !='register'){
           return redirect('/')->with('fail','You must login!');
       }
       if(session()->has('user') && ($request->path() == '/' || $request->path() == 'register')
       ){
           return back();
       }
    
    $response = $next($request);
    $headers = [
        'Cache-Control'      => 'no-cache, no-store, max-age=0, must-revalidate',
        'pragma'     => 'no-cache',
        'Expires' => 'Sat 01 Jan 1990 00:00:00 GMT',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS',
        'Access-Control-Allow-Headers' => '*',
    ];

    foreach($headers as $key => $value) {
        $response->headers->set($key, $value);
    }

    return $response;
    }


    
}
