<?php

namespace App\Core\Contracts\Middleware;

use App\Http\Request\Request;

interface Middleware
{
    /**
     * Handles incoming request before accessing controllers.
     *
     * @param mixed $request
     * @return mixed
     */
    public function handle(Request $request);
}