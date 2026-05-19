<?php

class DiscoveryController {
    
    public function index() {
        $base_path = '/infravision';
        
        require 'app/views/layout/header.php';
        require 'app/views/discovery/index.php';
        require 'app/views/layout/footer.php';
    }
}
