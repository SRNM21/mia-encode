<?php

namespace App\Http\Middlewares;

use App\Core\Facades\Auth;
use App\Core\Facades\Route;
use App\Core\Contracts\Middleware\Middleware as MiddlewareContract;

class AdminMiddleware implements MiddlewareContract
{
    /**
     * Handles incoming request before accessing controllers.
     *
     * @param mixed $request
     * @return mixed
     */
    public function handle($request)
    {
        if (!Auth::isAdmin())
        {
            Route::redirect('/encode');
        }
    }
}