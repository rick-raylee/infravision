<?php

class UpsController {

    private static function normalizarAutonomia($valor): ?int {
        if ($valor === null || $valor === '') {
            return null;
        }
        $minutos = (int)round((float)$valor);
        if ($minutos <= 0 || $minutos >= 65535 || $minutos >= 71582700 || $minutos > 10080) {
            return null;
        }
        return $minutos;
    }

    private static function normalizarPercentual($valor): ?int {
        if ($valor === null || $valor === '') {
            return null;
        }
        return (int)min(100, max(0, round((float)$valor)));
    }
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $nobreaks = [];
        if ($db) {
            $query = "SELECT d.*, 
                      (SELECT l.valor FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.dispositivo_id = d.id AND s.tipo = 'bateria' ORDER BY l.data_leitura DESC LIMIT 1) as bateria,
                      (SELECT l.valor FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.dispositivo_id = d.id AND s.tipo = 'tensao' ORDER BY l.data_leitura DESC LIMIT 1) as tensao,
                      (SELECT l.valor FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.dispositivo_id = d.id AND s.tipo = 'carga_nobreak' ORDER BY l.data_leitura DESC LIMIT 1) as carga,
                      (SELECT l.valor FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.dispositivo_id = d.id AND s.tipo = 'uptime' ORDER BY l.data_leitura DESC LIMIT 1) as autonomia_raw
                      FROM dispositivos d 
                      WHERE d.tipo = 'nobreak'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                if (preg_match('/^\d+$/', trim($row['nome'] ?? ''))) {
                    $row['nome'] = 'Nobreak USB';
                }
                $row['bateria'] = self::normalizarPercentual($row['bateria']);
                $row['carga'] = self::normalizarPercentual($row['carga']);
                $row['autonomia'] = self::normalizarAutonomia($row['autonomia_raw']);
                $tensao = $row['tensao'] !== null ? (float)$row['tensao'] : null;
                $row['tensao'] = ($tensao !== null && $tensao > 0 && $tensao < 500) ? (int)round($tensao) : null;
                unset($row['autonomia_raw']);
                $nobreaks[] = $row;
            }
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/ups/index.php';
        require 'app/views/layout/footer.php';
    }
}
