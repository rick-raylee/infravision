<?php

class TrafficController {
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $conexoes = [];
        if ($db) {
            $query = "SELECT origem, ip_origem as ip, destino, servico, CONCAT(latencia, 'ms') as latency, carga as load FROM conexoes ORDER BY atualizado_em DESC LIMIT 50";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $conexoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/traffic/index.php';
        require 'app/views/layout/footer.php';
    }
}
