<?php

class ComputerController {
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $computadores = [];
        if ($db) {
            $query = "SELECT d.*, 
                             (SELECT l.valor FROM leituras l 
                              JOIN sensores s ON l.sensor_id = s.id 
                              WHERE s.dispositivo_id = d.id AND s.tipo = 'cpu' 
                              ORDER BY l.data_leitura DESC LIMIT 1) as cpu,
                             (SELECT l.valor FROM leituras l 
                              JOIN sensores s ON l.sensor_id = s.id 
                              WHERE s.dispositivo_id = d.id AND s.tipo = 'ram' AND s.nome = 'RAM Livre (MB)' 
                              ORDER BY l.data_leitura DESC LIMIT 1) as ram

                      FROM dispositivos d
                      WHERE d.tipo = 'computador'
                      ORDER BY d.criado_em DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $computadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/computer/index.php';
        require 'app/views/layout/footer.php';
    }

    public function updatePeripherals() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();

            $id = $_POST['id'] ?? null;
            $tipo_periferico = $_POST['tipo_periferico'] ?? ''; // 'mouse' ou 'teclado'
            $data = $_POST['data'] ?? date('Y-m-d');

            if ($id && $db) {
                $params = [$data, $id];

                if ($tipo_periferico === 'mouse') {
                    $query = "UPDATE dispositivos SET mouse_trocado_em = ? WHERE id = ?";
                } elseif ($tipo_periferico === 'teclado') {
                    $query = "UPDATE dispositivos SET teclado_trocado_em = ? WHERE id = ?";
                } elseif ($tipo_periferico === 'entrega_save') {
                    $query = "UPDATE dispositivos SET data_entrega = ? WHERE id = ?";
                } elseif ($tipo_periferico === 'entrega_delete') {
                    $query = "UPDATE dispositivos SET data_entrega = NULL WHERE id = ?";
                    $params = [$id];
                } elseif ($tipo_periferico === 'funcionario_save') {
                    $query = "UPDATE dispositivos SET funcionario = ?, setor = ? WHERE id = ?";
                    $params = [$_POST['funcionario'] ?? '', $_POST['setor'] ?? '', $id];
                } elseif ($tipo_periferico === 'funcionario_delete') {
                    $query = "UPDATE dispositivos SET funcionario = NULL, setor = NULL WHERE id = ?";
                    $params = [$id];
                }

                if (isset($query)) {
                    $stmt = $db->prepare($query);
                    $stmt->execute($params);
                }
            }
        }

        header("Location: $base_path/computers");
        exit;
    }
}
