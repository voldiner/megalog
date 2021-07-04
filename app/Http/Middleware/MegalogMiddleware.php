<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

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
        /*$password = User::where('name',$request->input('login'))->first()->password;

        if ($request->input('password') === $password){
           return $next($request);
       }*/
        // --- аутентификация
        if (Auth::attempt([
            'email' => $request->input('login'),
            'password' => $request->input('password'),
        ])){

            return $next($request);
        };

       return response()->json(['error' => true, 'message' => 'Unauthorized Error', 'login' =>$request->input('login'), 'password' => $request->input('password') ], 401);
    }
}
