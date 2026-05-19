<?php

class ServerController {
    
    public function index() {
        require 'app/models/Device.php';
        $database = new Database();
        $db = $database->getConnection();
        $deviceModel = new Device($db);

        // Buscar todos os servidores cadastrados no banco com métricas reais
        $servidores_db = $deviceModel->getAllWithMetrics();
        
        // Mapear para o formato esperado pela view
        $servidores = [];
        foreach ($servidores_db as $s) {
            $servidores[] = [
                'id' => $s['id'],
                'nome' => $s['hostname'],
                'ip' => $s['ip'],
                'so' => htmlspecialchars($s['tipo']) . ' (Ativo)',
                'status' => $s['status'],
                'cpu' => $s['cpu_atual'] !== null ? round($s['cpu_atual']) : 0,
                'ram' => $s['ram_atual'] !== null ? round($s['ram_atual']) : 0
            ];
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        $current_path = '/servers';
        
        require 'app/views/layout/header.php';
        require 'app/views/server/index.php';
        require 'app/views/layout/footer.php';
    }

    public function details() {
        $server_name = $_GET['nome'] ?? 'Servidor Desconhecido';
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        $current_path = '/servers';
        
        require 'app/views/layout/header.php';
        require 'app/views/server/details.php';
        require 'app/views/layout/footer.php';
    }
}
