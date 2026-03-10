<?php

namespace App\Core\Contracts\Middleware;

interface Middleware
{
    /**
     * Handles incoming request before accessing controllers.
     *
     * @param mixed $request
     * @return mixed
     */
    public function handle($request);
}