<?php

class ReportController {
    
    public function inventory() {
        if (($_SESSION['usuario_nivel'] ?? '') === '') {
            header("Location: /infravision/login");
            exit;
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        require 'app/views/report/inventory.php';
    }
}
