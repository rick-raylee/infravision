<?php

class TrafficController {
    
    public function index() {
        $base_path = '/infravision';
        
        require 'app/views/layout/header.php';
        require 'app/views/traffic/index.php';
        require 'app/views/layout/footer.php';
    }
}
