<?php

class LogCenterController {
    
    public function index() {
        $base_path = '/infravision';
        
        require 'app/views/layout/header.php';
        require 'app/views/logcenter/index.php';
        require 'app/views/layout/footer.php';
    }
}
