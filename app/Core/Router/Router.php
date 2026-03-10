<?php

namespace App\Core\Router;

use App\Core\Contracts\Middleware\Middleware;
use App\Core\Controllers\Controller;
use App\Core\Support\Session;
use App\Http\Request\Request;
use App\Core\Container\Container;
use Exception;

class Router
{   
    /**
     * Controller instance string class.
     *
     * @var string
     */
    protected ?string $controller = null;

    /**
     * Middleware instance.
     *
     * @var Middleware
     */
    protected ?Middleware $middleware = null;

    /**
     * Container instance.
     *
     * @var Container
     */
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /*
    --------------------------------------------------------------------------
        HTTP Methods
    --------------------------------------------------------------------------
    */

    /**
     * Handles GET request method.
     *
     * @param string $path
     * @param Controller|string|array $controller
     * @return void
     */
    public function get(string $path = '/', $controller = null)
    {
        $this->handle('GET', $path, $controller);
    }
    
    /**
     * Handles POST request method.
     *
     * @param string $path
     * @param Controller|string|array $controller
     * @return void
     */
    public function post(string $path = '/',  $controller = null)
    { 
        $this->handle('POST', $path, $controller, $_POST);
    }

    /**
     * Handles PUT request method.
     *
     * @param string $path
     * @param Controller|string|array $controller
     * @return void
     */
    public function put(string $path = '/',  $controller = null)
    { 
        $this->handle('PUT', $path, $controller, $_POST);
    }

    /**
     * Handles PATCH request method.
     *
     * @param string $path
     * @param Controller|string|array $controller
     * @return void
     */
    public function patch(string $path = '/',  $controller = null)
    { 
        $this->handle('PATCH', $path, $controller, $_POST);
    }

    /**
     * Handles DELETE request method.
     *
     * @param string $path
     * @param Controller|string|array $controller
     * @return void
     */
    public function delete(string $path = '/',  $controller = null)
    { 
        $this->handle('DELETE', $path, $controller, $_POST);
    }
 
    /**
     * Handles all request method.
     *
     * @param string $method 
     * @param string $path
     * @param Controller|array|string $controller
     * @param array $data
     * @return void
     */
    public function handle(
        string $method = 'GET',
        string $path = '/',
        $controller = null,
        array $data = []
    ) {
        if (!$this->validRequest($method, $path)) {
            return;
        }

        $this->initializeRequestState();

        $callable = $this->resolveController($controller);

        $request = Request::capture();

        $this->runMiddleware($request);

        $this->dispatch($callable, $request, $data);
    }

    private function initializeRequestState(): void
    {
        Session::set('errors', []);
    }

    private function resolveController($controller): array
    {
        $parsed = $this->parseController($controller);

        if (!$parsed) {
            throw new \Exception(sprintf(
                "Invalid Controller '%s'",
                is_array($controller)
                    ? implode('@', $controller)
                    : (string) $controller
            ));
        }

        return $parsed;
    }

    private function runMiddleware(Request $request): void
    {
        if (!$this->middleware) {
            return;
        }

        $this->middleware->handle($request);
    }

    private function dispatch(array $callable, Request $request, array $data): void
    {
        $this->container->call(
            $callable,
            [$request, ...array_values($data)]
        );

        exit;
    }

    /*
    --------------------------------------------------------------------------
        Controller Resolution
    --------------------------------------------------------------------------
    */

    /**
     * Seperates the callable from the controller.
     *
     * @param Controller|string|array $controller
     * @return callable|false
     */
    private function parseController(Controller|string|array $controller)
    {
        if ($this->isControllerInvokable($controller)) 
        {
            // Handles Invokable controller
            $instance = $this->container->make($controller);

            return [$instance, '__invoke'];
        }
        else if ($this->isControllerArray($controller)) 
        {
            // Handles Controller function array
            [$class, $method] = $controller;

            $instance = $this->container->make($class);

            return [$instance, $method];
        }
        else if ($this->isControllerGroup($controller)) 
        {  
            // Handles from route group 
            $instance = $this->container->make($this->controller);

            return [$instance, $controller];
        }

        return false;
    }

    /*
    --------------------------------------------------------------------------
        Validation
    --------------------------------------------------------------------------
    */

    /**
     * Returns true if the request is valid, false otherwise.
     *
     * @param string $method
     * @param string $path
     * @return boolean
     */
    private function validRequest(string $method, string $path)
    {
        $current_uri = parse_url($_SERVER['REQUEST_URI'])['path'];
        $pattern = '#^' . env('ROOT') . $path . '$#';

        if ($method != $_SERVER['REQUEST_METHOD']) return false;
        
        // Checks if the current uri matches the passed uri
        return preg_match($pattern, $current_uri);
    }

    
    /**
     * Checks if the controller is callable array.
     *
     * @param mixed $controller
     * @return boolean
     */
    private function isControllerArray(mixed $controller)
    {
        return is_array($controller) && count($controller) === 2 && // Flag for callable function array, ex: `[class, method]`
            $this->isController($controller[0]) &&                  // Flag for controller class
            is_string($controller[1]);                              // Flag for controller method class
    }
    
    /**
     * Checks if the controller is invokable or one purpose.
     *
     * @param mixed $controller
     * @return boolean
     */
    private function isControllerInvokable(mixed $controller)
    {
        return $this->isController($controller);
    }

    /**
     * Checks if the controller is a method from the group controller.
     *
     * @param mixed $controller
     * @return boolean
     */
    private function isControllerGroup(mixed $controller)
    {
        return is_string($controller) && $this->controller;
    }

    /**
     * Check if the passed argument is an instance of a controller class.
     *
     * @param mixed $controller
     * @return boolean
     */
    private function isController(mixed $controller)
    {
        return is_subclass_of($controller, Controller::class);
    }

    /**
     * Check if the passed argument is an instance of a middleware class.
     *
     * @param mixed $middleware
     * @return boolean
     */
    private function isMiddleware(mixed $middleware)
    {
        return is_subclass_of($middleware, Middleware::class);
    }

    /*
    --------------------------------------------------------------------------
        Route Groups
    --------------------------------------------------------------------------
    */

    /**
     * Sets a default controller for router.
     *
     * @param string $controller
     * @return Router
     */
    public function controller(string $controller)
    {
        if (!$this->isController($controller))
        {
            throw new Exception("{$controller} is not a valid controller.");
        }

        $this->controller = $controller;

        return $this;
    }

    /**
     * Sets a default middleware for router.
     *
     * @param Middleware|string $middleware
     * @return Router
     */
    public function middleware(Middleware|string $middleware)
    {
        if (!$this->isMiddleware($middleware))
        {
            throw new Exception("{$middleware} is not a valid middleware.");
        }

        $this->middleware = $this->container->make($middleware);

        return $this;
    }

    /**
     * Initialize a group route for the specified controller.
     *
     * @param callable $routes
     * @return void
     */
    public function group(callable $routes)
    {   
        if (!$this->controller && !$this->middleware)
        {
            return false;
        }

        $routes();
    }

    /*
    --------------------------------------------------------------------------
        Redirects
    --------------------------------------------------------------------------
    */

    /**
     * Redirect to the url.
     *
     * @param string $url
     * @param int $status_code
     * @param array $data
     * @return void
     */
    public function redirect(string $url, int $status_code = 303, array $data = [])
    {
        // Store the passed data in session, and will  
        // be retreive later by another controller
        Session::set('PASSED_DATA', $data); 
        header('Location: ' . env('ROOT') . $url, true, $status_code);
        exit;
    }

    /**
     * Redirect to the previous url.
     *
     * @return void
     */
    public function back()
    {
        $this->redirect(Session::get('previous_url'));
    }
}