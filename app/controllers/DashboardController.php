<?php

class DashboardController {
    
    public function index() {
        require 'app/models/Device.php';
        
        $database = new Database();
        $db = $database->getConnection();
        $deviceModel = new Device($db);

        // Buscar estatísticas reais do banco
        $total_devices = $deviceModel->countByStatus('online') + $deviceModel->countByStatus('alerta') + $deviceModel->countByStatus('offline');
        
        $estatisticas = [
            'servidores_online' => $deviceModel->countByStatus('online'),
            'servidores_total' => $total_devices,
            'alertas_ativos' => $deviceModel->countByStatus('alerta') + $deviceModel->countByStatus('offline'),
            'vms_total' => 18 // Mock por enquanto (seria via vCenter API)
        ];
        
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        $current_path = '/dashboard';
        
        require 'app/views/layout/header.php';
        require 'app/views/dashboard/index.php';
        require 'app/views/layout/footer.php';
    }
}
