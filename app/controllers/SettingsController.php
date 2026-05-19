<?php

class SettingsController {
    
    public function index() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: /infravision/dashboard");
            exit;
        }

        $base_path = '/infravision';
        require 'app/views/layout/header.php';
        require 'app/views/settings/index.php';
        require 'app/views/layout/footer.php';
    }
}
