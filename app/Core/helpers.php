<?php

use App\Core\Support\PublicImageManager;
use App\Core\Support\Session;

if (!function_exists('path_parser'))
{
    /**
     * Returns a view file path.
     *
     * @param string $view
     * @return string
     */ 
    function path_parser(string $path)
    {
        return str_replace('.', '/', $path);
    }
}

if (!function_exists('get_view'))
{
    /**
     * Returns a view file path.
     *
     * @param string $view
     * @return string
     */ 
    function get_view(string $view)
    {   
        return config('app.resources.views') . path_parser($view) . '.php';
    }
}

if (!function_exists('get_component'))
{
    /**
     * Returns a component file path.
     *
     * @param string $component
     * @return string
     */ 
    function get_component(string $component, array $data = [])
    {
        extract($data);
        require get_view('components.' . $component);
    }
}

if (!function_exists('get_modal'))
{
    /**
     * Returns a modal file path.
     *
     * @param string $modal
     * @return string
     */ 
    function get_modal(string $modal, array $data = [])
    {
        extract($data);
        require get_view('components.modals.' . $modal);
    }
}

if (!function_exists('get_icon'))
{
    /**
     * Returns a icon file path.
     *
     * @param string $icon
     * @return string
     */ 
    function get_icon(string $icon)
    {
        get_component('icons.' . $icon);
    }
}

if (!function_exists('get_css'))
{
    /**
     * Returns a css file path.
     *
     * @param string $css
     * @return string
     */ 
    function get_css(string $css)
    {
        return config('app.resources.css') . path_parser($css) . '.css';
    }
}

if (!function_exists('get_js'))
{
    /**
     * Returns a javascript file path.
     *
     * @param string $js
     * @return string
     */ 
    function get_js(string $js)
    {
        return config('app.resources.js') . path_parser($js) .".js";
    }
}

if (!function_exists('get_image'))
{
    /**
     * Returns an image file path.
     *
     * @param string $img
     * @return string
     */ 
    function get_image(string $img)
    {
        return config('app.resources.images') . $img;
    }
}

if (!function_exists('get_favicon'))
{
    /**
     * Gets the favicon icon.
     *
     * @param ?string $favicon
     * @return string
     */
    function get_favicon(?string $favicon = null)
    {
        return config('app.public') . ($favicon ?? 'favicon.ico');
    }
}

if (!function_exists('public_img_manager'))
{
    /**
     * Returns an instance of PublicImageManager.
     *
     * @return PublicImageManager
     *
     */
    function public_img_manager(): PublicImageManager
    {
        return new PublicImageManager();
    }
}

if (!function_exists('public_img_url'))
{
    /**
     * Returns a public image url.
     *
     * @param string $img
     * @return string
     */
    function public_img_url(string $img)
    {
        return url('/' . config('app.public_img') . $img);
    }
}

if (!function_exists('css'))
{
    /**
     * Returns a partial stylesheet of the css file.
     *
     * @param string $css
     * @return string
     */
    function css(string $css)
    {
        return "
            <link rel='stylesheet' href='" . get_css('vendor.jquery-ui.jquery-ui') ."'>
            <link rel='stylesheet' href='" . get_css('vendor.jquery-ui.jquery-ui.theme') ."'>
            <link rel='stylesheet' href='" . get_css('vendor.jquery-ui.jquery-ui.stucture') ."'>
            <link rel='stylesheet' href='" . get_css($css) ."'>
        ";
    }
}

if (!function_exists('js'))
{
    /**
     * Returns a partial script of the javascript file
     *
     * @param string $js
     * @return string
     */
    function js(string $js)
    {
        return "
            <script type='module' src='" . get_js($js) . "'></script>
        ";
    }
}

if (!function_exists('js_jq'))
{
    /**
     * Returns a partial script of the javascript file including the jquery 
     *
     * @param string $js
     * @return string
     */
    function js_jq(string $js)
    {
        // jquery-3.7.1
        return "
            <script type='text/javascript' src='" . get_js('vendor.jquery') . "'></script>
            <script type='text/javascript' src='" . get_js('vendor.jquery-ui.jquery-ui') . "'></script>
            <script type='module' src='" . get_js('utils.utils') . "'></script>
            <script type='module' src='" . get_js($js) . "'></script>
        ";
    }
}

if (!function_exists('env'))
{
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @return mixed
     */
    function env(string $key)
    {
        if (!isset($_ENV[$key]))
        {
            throw new RuntimeException("[ENV] No '$key' key found in environment variables.");
        }

        return $_ENV[$key];
    }
}

if (!function_exists('is_associative'))
{
    /**
     * Returns true if the array is associative.
     *
     * @param mixed $array
     * @return boolean
     */
    function is_associative($array)
    {   
        if (!is_array($array)) return false;
        
        return array_keys($array) !== range(0, count($array) - 1);    
    }
}

if (!function_exists('config'))
{
    /**
     * Gets the value of configuration variable. The first 
     * word in seperator must be a config file.
     *
     * @param string $key
     * @param mix $default
     * @return mixed
     */
    function config(string $key, $default = null)
    { 
        // Seperate the keys to get the first word or filename, 
        // which then can be used to include the config file.
        $keys = explode('.', $key);
        $value = include "config/$keys[0].php";
        
        // Combine the seperated keys into a string that can be 
        // pass to the finder function.
        $keys = implode('.', array_slice($keys, 1));
        return get_nested_value($value, $keys, $default);
    }
}
 
if (!function_exists('string_format'))
{
    /**
     * Returns a formatted template based on the given replacer.
     *
     * @param string $template
     * @param array $replacer
     * @return string
     */
    function string_format(string $template, array $replacer)
    {
        foreach ($replacer as $placeholder => $value) 
        {
            $template = str_replace("$placeholder", $value, $template);
        }
        
        return $template;
    }
}

if (!function_exists('url'))
{
    /**
     * Returns a relative path.
     *
     * @param string $path
     * @return string
     */
    function url(string $path)
    {
        return env('ROOT') . $path;
    }
}

if (!function_exists('get_error'))
{
    /**
     * Gets an error value.
     *
     * @param string $key
     * @return array
     */
    function get_error(string $key = '*'): array
    {
        if ($key === '*')
        {
            return Session::get('errors');
        }

        return get_nested_value(Session::getSession(), 'errors.' . $key);
    }
}

if (!function_exists('set_error'))
{
    /**
     * Sets an error value.
     *
     * @param string $key
     * @param array $value
     * @return void
     */
    function set_error(string $key, array $value)
    {
        Session::set('errors', [
            ...get_error(),
            $key => $value
        ]);
    }
}

if (!function_exists('generate_token'))
{
    /**
     * Generates a random token.
     *
     * @param int $length
     * @return string
     */
    function generate_token(int $length = 128)
    {
        $bytes = random_bytes($length);
        return bin2hex($bytes);
    }
}

if (!function_exists('get_nested_value'))
{
    /**
     * Returns the value of the key from the nested array.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function get_nested_value(array $array, string $key, $default = null)
    {
        // Converts the string keys into array of keys.
        $keys = explode('.', $key);

        // Iterate into each nested array.
        foreach ($keys as $key) 
        {
            if (isset($array[$key])) 
            {
                $array = $array[$key];
            } 
            else 
            {
                // Return default value if not found.
                return $default;
            }
        }
    
        return $array;
    }
}

if (!function_exists('model_list_to_array'))
{
    /**
     * Converts a list of models into an array of arrays.
     *
     * @param array $models
     * @return array
     */
    function model_list_to_array($modelClass, array $models)
    {
        return array_map(fn($m) => (new $modelClass($m))->toArray(), $models);  
    }
}

if (!function_exists('sort_by'))
{
    /**
     * Sorts an array of models by a specified key.
     *
     * @param array $models
     * @return array
     */
    function sort_by(array $models, string $key)
    {
        usort($models, fn($a, $b) => strcmp($a[$key] ?? '', $b[$key] ?? ''));
        return $models;
    }
}

if (!function_exists('format_date'))
{
    /**
     * Formats a date string into a readable format.
     *
     * @param string|null $dateString
     * @return string
     */
    function formatDate(?string $dateString, string $format = 'M j, Y'): string
    {
        try
        {
            if ($dateString == '' || $dateString == null) 
            {
                return '';
            }

            $date = new DateTime($dateString);
            return $date->format($format);
        } 
        catch (Exception $e) 
        {
            return '';
        }
    }
}

if (!function_exists('dd'))
{
    /**
     * var_dump and die.
     *
     * @param mixed $data
     * @return boolean
     */
    function dd($data)
    {   
        var_dump($data);
        die();
    }
}

if (!function_exists('timeAgo'))
{
    function timeAgo($datetime) 
    {
        $now = time();
        $past = strtotime($datetime);

        $seconds = $now - $past;

        if ($seconds < 60) 
        {
            return $seconds . 's';
        }

        $minutes = floor($seconds / 60);
        if ($minutes < 60) 
        {
            return $minutes . 'm';
        }

        $hours = floor($minutes / 60);
        if ($hours < 24) 
        {
            return $hours . 'h';
        }

        $days = floor($hours / 24);
        return $days . 'd';
    }
}