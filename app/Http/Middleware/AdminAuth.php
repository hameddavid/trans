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
       return $next($request)
       ->header('Access-Control-Allow-Origin','*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
       ->header('Cache-Control','no-cache, no-store, max-age=0, must-revalidate')
       ->header('pragma','no-cache')
       ->header('Expires','Sat 01 Jan 1990 00:00:00 GMT');
    }
}
