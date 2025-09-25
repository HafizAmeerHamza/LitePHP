<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index() {
        $data = [
            'title' => 'Home Page',
            'description' => 'This is home page',
        ];
        
        $this->view('pages/home', $data);
    }
}