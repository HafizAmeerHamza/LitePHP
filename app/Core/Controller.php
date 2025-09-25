<?php

namespace App\Core;

class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    protected function view($view, $data = []) {
        View::renderWithLayout($view, $data);
    }
    
    protected function render($view, $data = []) {
        View::render($view, $data);
    }
    
    protected function redirect($url) {
        View::redirect($url);
    }
    
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}