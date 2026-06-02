<?php

class DashboardController {
    
    public function index() {
        require 'app/models/Device.php';
        
        $database = new Database();
        $db = $database->getConnection();
        $deviceModel = new Device($db);

        // Buscar estatísticas reais do banco
        $total_devices = 0;
        $stmtTotal = $db->query("SELECT COUNT(*) FROM dispositivos");
        if ($stmtTotal) {
            $total_devices = (int)$stmtTotal->fetchColumn();
        }

        $servidores_total = 0;
        $stmtServTotal = $db->query("SELECT COUNT(*) FROM dispositivos WHERE tipo IN ('servidor_windows', 'servidor_linux')");
        if ($stmtServTotal) {
            $servidores_total = (int)$stmtServTotal->fetchColumn();
        }

        $servidores_online = 0;
        $stmtServOnline = $db->query("SELECT COUNT(*) FROM dispositivos WHERE tipo IN ('servidor_windows', 'servidor_linux') AND status = 'online'");
        if ($stmtServOnline) {
            $servidores_online = (int)$stmtServOnline->fetchColumn();
        }
        
        $conexoes_ativas = 0;
        $stmtConn = $db->query("SELECT COUNT(*) FROM conexoes c JOIN dispositivos d ON c.dispositivo_id = d.id WHERE d.status = 'online'");
        if ($stmtConn) {
            $conexoes_ativas = (int)$stmtConn->fetchColumn();
        }

        $stmtAlertas = $db->query("SELECT COUNT(*) FROM alertas WHERE status = 'ativo'");
        $alertas_ativos = $stmtAlertas ? (int)$stmtAlertas->fetchColumn() : 0;

        $estatisticas = [
            'servidores_online' => $servidores_online,
            'servidores_total' => $servidores_total,
            'alertas_ativos' => $alertas_ativos,
            'conexoes_ativas' => $conexoes_ativas,
            'vms_total' => $total_devices,
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
