<?php

class RuleController {
    
    public function index() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: /infravision/dashboard");
            exit;
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        require 'app/views/layout/header.php';
        require 'app/views/rule/index.php';
        require 'app/views/layout/footer.php';
    }
}
