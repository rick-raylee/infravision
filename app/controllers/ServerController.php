<?php

class ServerController {
    
    public function index() {
        require 'app/models/Device.php';
        $database = new Database();
        $db = $database->getConnection();
        $deviceModel = new Device($db);

        // Buscar apenas servidores cadastrados no banco com métricas reais
        $query = "SELECT d.id, d.nome as hostname, d.ip, d.tipo, d.status, d.ultimo_check,
                         (SELECT l.valor FROM leituras l 
                          JOIN sensores s ON l.sensor_id = s.id 
                          WHERE s.dispositivo_id = d.id AND s.tipo = 'cpu' 
                          ORDER BY l.data_leitura DESC LIMIT 1) as cpu_atual,
                         (SELECT l.valor FROM leituras l 
                          JOIN sensores s ON l.sensor_id = s.id 
                          WHERE s.dispositivo_id = d.id AND s.tipo = 'ram' 
                          ORDER BY l.data_leitura DESC LIMIT 1) as ram_atual
                  FROM dispositivos d
                  WHERE d.tipo IN ('servidor_windows', 'servidor_linux')
                  ORDER BY d.criado_em DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $servidores_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mapear para o formato esperado pela view
        $servidores = [];
        foreach ($servidores_db as $s) {
            $servidores[] = [
                'id' => $s['id'],
                'nome' => $s['hostname'],
                'ip' => $s['ip'],
                'so' => htmlspecialchars($s['tipo']) === 'servidor_linux' ? 'Linux Server' : 'Windows Server',
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
