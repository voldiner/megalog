<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class MegalogMiddleware
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
        $password = User::where('name',$request->input('login'))->first()->password;

        if ($request->input('password') === $password){
           return $next($request);
       }
       return response()->json(['error' => true, 'message' => 'Unauthorized Error'], 401);
    }
}
