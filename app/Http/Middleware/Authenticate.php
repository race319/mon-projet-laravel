<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Pour les API, toujours retourner null (pas de redirection)
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }
        
        // Pour le web, rediriger vers login (si la route existe)
        return route('login');
    }
}