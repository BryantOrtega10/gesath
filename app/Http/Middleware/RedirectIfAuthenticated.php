<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, ... $roles)
    {
        $rol = 0;
        $rutaNavegar = $request->path();
        if (Auth::check()) {
            $user = Auth::user();
            
            if (sizeof($roles) == 0) {
                $rol = $user->fkRol;
            } else {
                $valid = false;
                foreach($roles as $role) {
                    if ($role == $user->fkRol) {
                        $rol = $role;
                        $valid = true;
                        break;
                    } 
                }
                if(!$valid){
                    abort(403, 'No tienes autorización para ingresar.');
                }
            }
            
            switch($rol) {
                case 1:
                    if ($this->validarRol($rutaNavegar, $rol)) {
                        return redirect()->guest(route('portal/'));
                    } else {
                        return $next($request);
                    }
                break;
                case 2:
                    if ($this->validarRol($rutaNavegar, $rol)) {
                        return redirect()->guest(route('empleado/'));
                    } else {
                        return $next($request);
                    }
                case 3:
                    if ($this->validarRol($rutaNavegar, $rol)) {
                        return redirect()->guest(route('empleado/'));
                    } else {
                        return $next($request);
                    }
                break;
                default:
                    abort(403, 'No tienes autorización para ingresar.');
                break;

            }            
        } else {
            return $next($request);
        }
    }

    public function validarRol($ruta, $rol) {
        
        if ($ruta === '/') {
            return true;
        } else {
            return false;
        }
    }
}
