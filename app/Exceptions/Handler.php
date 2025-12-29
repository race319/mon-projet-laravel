<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Gérer les utilisateurs non authentifiés pour API + JWT
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Retourne toujours JSON 401 pour les API
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.'
        ], 401);
    }
}
