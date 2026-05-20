<?php

class ReportController {
    
    public function inventory() {
        if (($_SESSION['usuario_nivel'] ?? '') === '') {
            header("Location: " . BASE_PATH . "/login");
            exit;
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');

        require_once 'config/database.php';
        require_once 'app/models/Device.php';
        $db = (new Database())->getConnection();
        $dispositivos = $db ? (new Device($db))->getAllWithMetrics() : [];

        require 'app/views/report/inventory.php';
    }
}
