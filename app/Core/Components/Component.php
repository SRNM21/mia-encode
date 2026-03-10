<?php 

namespace App\Core\Components;

abstract class Component
{
    /**
     * Requires a view file 
     *
     * @param string $view view file for the route
     * @param array $data data for the page
     */
    protected static function view(string $view, array $data = [])
    {   
        extract($data);
        require get_view($view);
    }
}