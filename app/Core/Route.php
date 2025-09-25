<?php

namespace App\Core;

use App\Core\View;
use App\Core\RouteDefinition;

class Route {
    private static $routes = [];
    private static $namedRoutes = [];
    private static $groupStack = [];

    public static function get($path, $callback) {
        return self::addRoute('GET', $path, $callback);
    }

    public static function post($path, $callback) {
        return self::addRoute('POST', $path, $callback);
    }

    public static function put($path, $callback) {
        return self::addRoute('PUT', $path, $callback);
    }

    public static function delete($path, $callback) {
        return self::addRoute('DELETE', $path, $callback);
    }

    public static function patch($path, $callback) {
        return self::addRoute('PATCH', $path, $callback);
    }

    public static function options($path, $callback) {
        return self::addRoute('OPTIONS', $path, $callback);
    }

    /**
     * Register multiple HTTP verbs for the same route
     */
    public static function match($methods, $path, $callback) {
        $methods = is_array($methods) ? $methods : [$methods];
        $routeDefinition = null;
        
        foreach ($methods as $method) {
            $routeDefinition = self::addRoute(strtoupper($method), $path, $callback);
        }
        
        return $routeDefinition;
    }

    /**
     * Register a route for all HTTP verbs
     */
    public static function any($path, $callback) {
        return self::match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $path, $callback);
    }

    /**
     * Create a route group with shared attributes
     */
    public static function group($attributes, $callback) {
        self::$groupStack[] = $attributes;
        
        if (is_callable($callback)) {
            $callback();
        }
        
        array_pop(self::$groupStack);
    }

    /**
     * Create a route group with prefix
     */
    public static function prefix($prefix) {
        return new RouteGroup(['prefix' => $prefix]);
    }

    /**
     * Create a route group with middleware
     */
    public static function middleware($middleware) {
        return new RouteGroup(['middleware' => $middleware]);
    }

    /**
     * Create a route group with name prefix
     */
    public static function name($name) {
        return new RouteGroup(['as' => $name]);
    }

    private static function addRoute($method, $path, $callback) {
        // Normalize path (add leading slash if missing)
        $path = self::normalizePath($path);
        
        // Apply group attributes
        $groupAttributes = self::mergeGroupAttributes();
        
        // Apply prefix from groups
        if (!empty($groupAttributes['prefix'])) {
            $prefix = self::normalizePath($groupAttributes['prefix']);
            $path = rtrim($prefix, '/') . $path;
        }
        
        // Store route
        self::$routes[$method][$path] = [
            'callback' => $callback,
            'attributes' => $groupAttributes
        ];
        
        return new RouteDefinition($method, $path, $callback, $groupAttributes);
    }

    /**
     * Normalize path by ensuring it starts with /
     */
    private static function normalizePath($path) {
        // Handle empty path
        if (empty($path) || $path === '/') {
            return '/';
        }
        
        // Add leading slash if missing
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        
        return $path;
    }

    /**
     * Merge attributes from group stack
     */
    private static function mergeGroupAttributes() {
        $attributes = [];
        
        foreach (self::$groupStack as $group) {
            $attributes = array_merge($attributes, $group);
        }
        
        return $attributes;
    }

    public static function url($name, $params = []) {
        if (!isset(self::$namedRoutes[$name])) {
            throw new \Exception("Route name '{$name}' not defined.");
        }

        $path = self::$namedRoutes[$name];

        // Replace placeholders like {id} with actual params
        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }

        return $path;
    }

    public static function registerName($name, $path) {
        self::$namedRoutes[$name] = $path;
    }

    public static function getNamedRoutes()
    {
        return self::$namedRoutes;
    }

    public static function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash (except root)
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        if (isset(self::$routes[$method][$path])) {
            $routeData = self::$routes[$method][$path];
            $callback = $routeData['callback'];
            $attributes = $routeData['attributes'] ?? [];

            // Create Request instance
            $request = new Request();

            // Handle middleware (if implemented)
            // You can extend this to run middleware before the controller

            if (is_array($callback) && count($callback) === 2) {
                // Laravel-style array: [Controller::class, 'method']
                [$controllerClass, $methodName] = $callback;

                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass();
                    if (method_exists($controllerInstance, $methodName)) {
                        // Inject Request instance into controller method
                        $response = call_user_func([$controllerInstance, $methodName], $request);
                        return self::handleResponse($response);
                    } else {
                        self::handleError(500, "Method {$methodName} not found in {$controllerClass}");
                    }
                } else {
                    self::handleError(500, "Controller class {$controllerClass} not found");
                }

            } elseif (is_string($callback) && strpos($callback, '@') !== false) {
                list($controllerName, $methodName) = explode('@', $callback);
                $controllerClass = 'App\\Controllers\\' . $controllerName;

                if (!class_exists($controllerClass)) {
                    self::handleError(500, "Controller class $controllerClass not found");
                }

                $controller = new $controllerClass();

                if (!method_exists($controller, $methodName)) {
                    self::handleError(500, "Method $methodName not found in controller $controllerClass");
                }

                // Inject Request instance into controller method
                $response = call_user_func([$controller, $methodName], $request);
                return self::handleResponse($response);
            } elseif (is_callable($callback)) {
                $response = call_user_func($callback, $request);
                return self::handleResponse($response);
            } else {
                self::handleError(500, "Invalid route callback type");
            }

        } else {
            self::handleError(404, "Page not found");
        }
    }

    private static function handleError($code, $message) {
        http_response_code($code);
        if (file_exists("../resources/views/errors/{$code}.php")) {
            View::render("errors/{$code}", ['message' => $message]);
        } else {
            echo "<h1>Error {$code}</h1><p>{$message}</p>";
        }
    }

    /**
     * Handle different types of responses like Laravel
     */
    private static function handleResponse($response)
    {
        // If response is null or empty, do nothing
        if ($response === null) {
            return;
        }

        // If it's already a string, just echo it
        if (is_string($response)) {
            echo $response;
            return;
        }

        // If it's an array or object, convert to JSON
        if (is_array($response) || is_object($response)) {
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // If it's a boolean, convert to JSON
        if (is_bool($response)) {
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // If it's a number, convert to string
        if (is_numeric($response)) {
            echo (string) $response;
            return;
        }

        // For anything else, try to convert to string
        echo (string) $response;
    }
}

/**
 * Route Group Helper Class
 */
class RouteGroup {
    private $attributes = [];

    public function __construct($attributes = []) {
        $this->attributes = $attributes;
    }

    public function prefix($prefix) {
        $this->attributes['prefix'] = $prefix;
        return $this;
    }

    public function middleware($middleware) {
        $this->attributes['middleware'] = $middleware;
        return $this;
    }

    public function name($name) {
        $this->attributes['as'] = $name;
        return $this;
    }

    public function group($callback) {
        Route::group($this->attributes, $callback);
        return $this;
    }
}