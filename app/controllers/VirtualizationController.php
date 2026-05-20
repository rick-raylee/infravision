<?php

class VirtualizationController {
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');

        require_once 'config/database.php';
        require_once 'app/models/Device.php';
        $hosts = [];
        $db = (new Database())->getConnection();
        if ($db) {
            $hosts = (new Device($db))->getAllWithMetrics();
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/virtualization/index.php';
        require 'app/views/layout/footer.php';
    }
}
