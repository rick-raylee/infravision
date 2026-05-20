<?php

class DiscoveryController {
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');

        require_once 'config/database.php';
        require_once 'app/models/Device.php';

        $dispositivos = [];
        $db = (new Database())->getConnection();
        if ($db) {
            $deviceModel = new Device($db);
            $dispositivos = $deviceModel->getAll();
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/discovery/index.php';
        require 'app/views/layout/footer.php';
    }
}
