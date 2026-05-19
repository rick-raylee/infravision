<?php

class ReportController {
    
    public function inventory() {
        if (($_SESSION['usuario_nivel'] ?? '') === '') {
            header("Location: /infravision/login");
            exit;
        }

        $base_path = '/infravision';
        require 'app/views/report/inventory.php';
    }
}
