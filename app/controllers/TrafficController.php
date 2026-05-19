<?php

class TrafficController {
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        require 'app/views/layout/header.php';
        require 'app/views/traffic/index.php';
        require 'app/views/layout/footer.php';
    }
}
