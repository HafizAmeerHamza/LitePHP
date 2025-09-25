<?php

namespace App\Core;

class Request
{
    protected $data = [];
    protected $files = [];
    protected $headers = [];
    protected $method;
    protected $uri;
    
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->parseHeaders();
        $this->parseRequest();
        $this->files = $_FILES ?? [];
    }
    
    /**
     * Parse request headers
     */
    private function parseHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        
        // Add content type if available
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        
        $this->headers = $headers;
    }
    
    /**
     * Parse incoming request data
     */
    private function parseRequest()
    {
        switch ($this->method) {
            case 'GET':
                $this->data = $_GET;
                break;
                
            case 'POST':
                $contentType = $this->header('Content-Type', '');
                
                if (strpos($contentType, 'application/json') !== false) {
                    $json = json_decode(file_get_contents('php://input'), true);
                    $this->data = is_array($json) ? $json : [];
                } else {
                    $this->data = $_POST;
                }
                break;
                
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $contentType = $this->header('Content-Type', '');
                
                if (strpos($contentType, 'application/json') !== false) {
                    $json = json_decode(file_get_contents('php://input'), true);
                    $this->data = is_array($json) ? $json : [];
                } else {
                    parse_str(file_get_contents('php://input'), $this->data);
                }
                break;
        }
    }
    
    /**
     * Get a specific input value
     */
    public function input($key = null, $default = null)
    {
        if ($key === null) {
            return $this->data;
        }
        
        return $this->data[$key] ?? $default;
    }
    
    /**
     * Get a specific input value (alias for input)
     */
    public function get($key = null, $default = null)
    {
        return $this->input($key, $default);
    }
    
    /**
     * Get all input data
     */
    public function all()
    {
        return $this->data;
    }
    
    /**
     * Get only specified keys from input
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $result = [];
        
        foreach ($keys as $key) {
            if ($this->has($key)) {
                $result[$key] = $this->input($key);
            }
        }
        
        return $result;
    }
    
    /**
     * Get all input except specified keys
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $result = $this->all();
        
        foreach ($keys as $key) {
            unset($result[$key]);
        }
        
        return $result;
    }
    
    /**
     * Check if input has a specific key
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }
    
    /**
     * Check if input has a specific key and it's not empty
     */
    public function filled($key)
    {
        return $this->has($key) && !empty($this->data[$key]);
    }
    
    /**
     * Get request method
     */
    public function method()
    {
        return $this->method;
    }
    
    /**
     * Check if request method is POST
     */
    public function isMethod($method)
    {
        return strtoupper($this->method) === strtoupper($method);
    }
    
    /**
     * Get request URI
     */
    public function uri()
    {
        return $this->uri;
    }
    
    /**
     * Get request URL path
     */
    public function path()
    {
        return parse_url($this->uri, PHP_URL_PATH);
    }
    
    /**
     * Get a header value
     */
    public function header($key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }
    
    /**
     * Get all headers
     */
    public function headers()
    {
        return $this->headers;
    }
    
    /**
     * Check if request expects JSON response
     */
    public function expectsJson()
    {
        $accept = $this->header('Accept', '');
        return strpos($accept, 'application/json') !== false;
    }
    
    /**
     * Check if request is AJAX
     */
    public function ajax()
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
    
    /**
     * Get uploaded file
     */
    public function file($key)
    {
        return $this->files[$key] ?? null;
    }
    
    /**
     * Check if request has file
     */
    public function hasFile($key)
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }
    
    /**
     * Validate request data
     */
    public function validate($rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $rules = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            
            foreach ($rules as $rule) {
                $error = $this->validateField($field, $rule);
                if ($error) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }
                    $errors[$field][] = $error;
                }
            }
        }
        
        if (!empty($errors)) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['errors' => $errors]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Validate individual field
     */
    private function validateField($field, $rule)
    {
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $ruleValue = $parts[1] ?? null;
        
        switch ($ruleName) {
            case 'required':
                if (!$this->filled($field)) {
                    return "The {$field} field is required.";
                }
                break;
                
            case 'email':
                if ($this->has($field) && !filter_var($this->input($field), FILTER_VALIDATE_EMAIL)) {
                    return "The {$field} must be a valid email address.";
                }
                break;
                
            case 'min':
                $value = $this->input($field);
                if ($this->has($field) && strlen($value) < intval($ruleValue)) {
                    return "The {$field} must be at least {$ruleValue} characters.";
                }
                break;
                
            case 'max':
                $value = $this->input($field);
                if ($this->has($field) && strlen($value) > intval($ruleValue)) {
                    return "The {$field} may not be greater than {$ruleValue} characters.";
                }
                break;
                
            case 'numeric':
                if ($this->has($field) && !is_numeric($this->input($field))) {
                    return "The {$field} must be a number.";
                }
                break;
                
            case 'url':
                if ($this->has($field) && !filter_var($this->input($field), FILTER_VALIDATE_URL)) {
                    return "The {$field} must be a valid URL.";
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Get client IP address
     */
    public function ip()
    {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                return trim($ips[0]);
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get user agent
     */
    public function userAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    /**
     * Convert request data to JSON
     */
    public function toJson()
    {
        return json_encode($this->all());
    }
    
    /**
     * Get raw input data
     */
    public function getContent()
    {
        return file_get_contents('php://input');
    }
    
    /**
     * Magic getter to access input data as properties
     * Allows $request->post_url instead of $request->get('post_url')
     */
    public function __get($key)
    {
        return $this->input($key);
    }
    
    /**
     * Magic setter to set input data as properties
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    /**
     * Magic isset to check if property exists
     */
    public function __isset($key)
    {
        return $this->has($key);
    }
    
    /**
     * Magic unset to remove property
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }
}