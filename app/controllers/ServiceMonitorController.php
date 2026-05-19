<?php

class ServiceMonitorController {
    
    public function index() {
        $base_path = '/infravision';
        
        require 'app/views/layout/header.php';
        require 'app/views/servicemonitor/index.php';
        require 'app/views/layout/footer.php';
    }
}
