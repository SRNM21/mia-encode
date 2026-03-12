<?php

namespace App\Core\Controllers;

use App\Core\Facades\Auth;
use App\Core\Facades\Route;
use App\Core\Support\Session;

abstract class Controller
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function __invoke()
    {
        // 
    }

    /**
     * Requires a view file 
     *
     * @param string $view view file for the route
     * @param array $data data for the page
     */
    protected function view(string $view, array $data = [])
    {   
        if (Auth::check()) $data['user'] = Auth::user();

        // Get and join session data (passed data using 'redirect' method of Route class)
        extract(array_merge($data, Session::get('PASSED_DATA', [])));
        require get_view($view);
    }

    /**
     * Return a JSON response using given data
     *
     * @param mixed $data data to encode in json
     */
    protected function responseJson($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'data' => $data
        ], JSON_THROW_ON_ERROR);

        exit;
    }

    protected function redirect(string $url, int $status = 303, array $data = []): void
    {
        Route::redirect($url, $status, $data);
    }
}