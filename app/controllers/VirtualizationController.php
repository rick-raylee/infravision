<?php

class VirtualizationController {
    
    public function index() {
        $base_path = '/infravision';
        
        require 'app/views/layout/header.php';
        require 'app/views/virtualization/index.php';
        require 'app/views/layout/footer.php';
    }
}
