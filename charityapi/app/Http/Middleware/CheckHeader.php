<?php

namespace App\Http\Middleware;  
  
use Closure;  
use Illuminate\Contracts\Auth\Guard;  
use Response;  
  
class CheckHeader  
{  
    /** 
     * The Guard implementation. 
     * 
     * @var Guard 
     */  
  
    /** 
     * Handle an incoming request. 
     * 
     * @param  \Illuminate\Http\Request  $request 
     * @param  \Closure  $next 
     * @return mixed 
     */  
    public function handle($request, Closure $next)  
    {  
        if(!isset($_SERVER['HTTP_X_TOKEN'])){  
            return Response::json(array('error'=>'Please add x-token header'));  
        }  
  
        if($_SERVER['HTTP_X_TOKEN'] != config('data.appToken')){  
            return Response::json(array('error'=>'wrong header'));  
        }  
  
        return $next($request);  
    }  
}