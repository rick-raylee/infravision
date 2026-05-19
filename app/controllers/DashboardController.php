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
            'vms_total' => $total_devices // usar total de servidores reais como contagem de VMs/Servidores
        ];

        // Buscar todos os dispositivos para a tabela do dashboard
        $dispositivos = $deviceModel->getAll();

        // Buscar top 5 consumo de CPU atual por dispositivo
        $queryCpu = "SELECT d.nome, l.valor FROM leituras l
                     JOIN sensores s ON l.sensor_id = s.id
                     JOIN dispositivos d ON s.dispositivo_id = d.id
                     WHERE s.tipo = 'cpu'
                       AND l.data_leitura = (
                           SELECT MAX(data_leitura) 
                           FROM leituras 
                           WHERE sensor_id = s.id
                       )
                     ORDER BY l.valor DESC LIMIT 5";
        $stmtCpu = $db->prepare($queryCpu);
        $stmtCpu->execute();
        $topCpu = $stmtCpu->fetchAll(PDO::FETCH_ASSOC);
        
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        $current_path = '/dashboard';
        
        require 'app/views/layout/header.php';
        require 'app/views/dashboard/index.php';
        require 'app/views/layout/footer.php';
    }
}
