<?php

class UpsController {
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $nobreaks = [];
        if ($db) {
            // Puxar dispositivos do tipo nobreak
            $query = "SELECT d.*, 
                      (SELECT l.valor FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.dispositivo_id = d.id AND s.tipo = 'bateria' ORDER BY l.data_leitura DESC LIMIT 1) as bateria,
                      (SELECT l.valor FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.dispositivo_id = d.id AND s.tipo = 'tensao' ORDER BY l.data_leitura DESC LIMIT 1) as tensao,
                      (SELECT l.valor FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.dispositivo_id = d.id AND s.tipo = 'carga_nobreak' ORDER BY l.data_leitura DESC LIMIT 1) as carga,
                      (SELECT l.valor FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.dispositivo_id = d.id AND s.tipo = 'uptime' ORDER BY l.data_leitura DESC LIMIT 1) as autonomia
                      FROM dispositivos d 
                      WHERE d.tipo = 'nobreak'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $nobreaks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/ups/index.php';
        require 'app/views/layout/footer.php';
    }
}
