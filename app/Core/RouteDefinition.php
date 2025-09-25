<?php

namespace App\Core;

class RouteDefinition {
    private $method;
    private $path;
    private $callback;
    private $attributes;

    public function __construct($method, $path, $callback, $attributes = []) {
        $this->method = $method;
        $this->path = $path;
        $this->callback = $callback;
        $this->attributes = $attributes;
    }

    public function name($name) {
        // Apply name prefix from group if exists
        $namePrefix = $this->attributes['as'] ?? '';
        $fullName = $namePrefix . $name;
        
        Route::registerName($fullName, $this->path);
        return $this;
    }

    public function middleware($middleware) {
        $this->attributes['middleware'] = $middleware;
        return $this;
    }

    public function where($parameter, $pattern) {
        if (!isset($this->attributes['where'])) {
            $this->attributes['where'] = [];
        }
        $this->attributes['where'][$parameter] = $pattern;
        return $this;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getPath() {
        return $this->path;
    }

    public function getCallback() {
        return $this->callback;
    }

    public function getAttributes() {
        return $this->attributes;
    }
}