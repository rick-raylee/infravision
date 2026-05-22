<?php

class ServerController {
    
    public function index() {
        require 'app/models/Device.php';
        $database = new Database();
        $db = $database->getConnection();
        $deviceModel = new Device($db);

        $query = "SELECT d.id, d.nome as hostname, d.ip, d.tipo, d.status, d.ultimo_check,
                         (SELECT l.valor FROM leituras l 
                          JOIN sensores s ON l.sensor_id = s.id 
                          WHERE s.dispositivo_id = d.id AND s.tipo = 'cpu' 
                          ORDER BY l.data_leitura DESC LIMIT 1) as cpu_atual,
                         (SELECT l.valor FROM leituras l 
                          JOIN sensores s ON l.sensor_id = s.id 
                          WHERE s.dispositivo_id = d.id AND s.tipo = 'ram' AND s.nome = 'RAM Livre (MB)' 
                          ORDER BY l.data_leitura DESC LIMIT 1) as ram_livre,
                         (SELECT l.valor FROM leituras l 
                          JOIN sensores s ON l.sensor_id = s.id 
                          WHERE s.dispositivo_id = d.id AND s.tipo = 'ram' AND s.nome = 'RAM Total (MB)' 
                          ORDER BY l.data_leitura DESC LIMIT 1) as ram_total
                  FROM dispositivos d
                  WHERE d.tipo IN ('servidor_windows', 'servidor_linux')
                  ORDER BY d.criado_em DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $servidores_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $servidores = [];
        foreach ($servidores_db as $s) {
            $ram_livre = $s['ram_livre'] !== null ? (float)$s['ram_livre'] : null;
            $ram_total = $s['ram_total'] !== null ? (float)$s['ram_total'] : null;
            
            $ram_percent = ($ram_livre !== null && $ram_total !== null && $ram_total > 0)
                ? (($ram_total - $ram_livre) / $ram_total) * 100
                : 0;
            $ram_total_gb = $ram_total !== null ? round($ram_total / 1024, 1) : 0;
            $ram_usada_gb = ($ram_total !== null && $ram_livre !== null) ? round(($ram_total - $ram_livre) / 1024, 1) : 0;

            $servidores[] = [
                'id' => $s['id'],
                'nome' => $s['hostname'],
                'ip' => $s['ip'],
                'so' => $s['tipo'] === 'servidor_linux' ? 'Linux Server' : 'Windows Server',
                'status' => $s['status'],
                'cpu' => $s['cpu_atual'] !== null ? round($s['cpu_atual']) : 0,
                'ram' => $ram_percent,
                'ram_total' => $ram_total_gb,
                'ram_usada' => $ram_usada_gb
            ];
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        $current_path = '/servers';
        
        require 'app/views/layout/header.php';
        require 'app/views/server/index.php';
        require 'app/views/layout/footer.php';
    }

    public function details() {
        $server_name = $_GET['nome'] ?? '';
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        $current_path = '/servers';

        $servidor = null;
        $historico_cpu = [];
        $historico_ram = [];
        $discos = [];

        if ($server_name !== '') {
            $database = new Database();
            $db = $database->getConnection();
            if ($db) {
                $stmt = $db->prepare("SELECT * FROM dispositivos WHERE nome = :nome LIMIT 1");
                $stmt->execute([':nome' => $server_name]);
                $servidor = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($servidor) {
                    // Obter a capacidade total de RAM mais recente para calcular percentual
                    $stmtTotal = $db->prepare("SELECT l.valor FROM leituras l 
                                               JOIN sensores s ON l.sensor_id = s.id 
                                               WHERE s.dispositivo_id = :id AND s.tipo = 'ram' AND s.nome = 'RAM Total (MB)'
                                               ORDER BY l.data_leitura DESC LIMIT 1");
                    $stmtTotal->execute([':id' => $servidor['id']]);
                    $ram_total = (float)($stmtTotal->fetchColumn() ?: 0);

                    $histQuery = "SELECT l.valor, l.data_leitura, s.tipo, s.nome
                                  FROM leituras l
                                  JOIN sensores s ON s.id = l.sensor_id
                                  WHERE s.dispositivo_id = :id 
                                    AND (s.tipo = 'cpu' OR (s.tipo = 'ram' AND s.nome = 'RAM Livre (MB)'))
                                  ORDER BY l.data_leitura DESC LIMIT 96";
                    $stmtHist = $db->prepare($histQuery);
                    $stmtHist->execute([':id' => $servidor['id']]);
                    $leituras = array_reverse($stmtHist->fetchAll(PDO::FETCH_ASSOC));
                    foreach ($leituras as $l) {
                        if ($l['tipo'] === 'cpu') {
                            $historico_cpu[] = round((float)$l['valor'], 1);
                        } elseif ($l['tipo'] === 'ram') {
                            $ram_livre = (float)$l['valor'];
                            if ($ram_total > 0) {
                                $historico_ram[] = round((($ram_total - $ram_livre) / $ram_total) * 100, 1);
                            } else {
                                $historico_ram[] = 0;
                            }
                        }
                    }

                    $stmtDisco = $db->prepare("SELECT s.nome, l.valor
                                               FROM leituras l
                                               JOIN sensores s ON s.id = l.sensor_id
                                               WHERE s.dispositivo_id = :id AND s.tipo = 'disco'
                                                 AND l.data_leitura = (
                                                     SELECT MAX(l2.data_leitura) FROM leituras l2 WHERE l2.sensor_id = s.id
                                                 )");
                    $stmtDisco->execute([':id' => $servidor['id']]);
                    $raw_discos = $stmtDisco->fetchAll(PDO::FETCH_ASSOC);

                    $discos_agrupados = [];
                    foreach ($raw_discos as $rd) {
                        if (preg_match('/Disco (Livre|Total) \(([^)]+)\) GB/i', $rd['nome'], $matches)) {
                            $tipo_campo = strtolower($matches[1]); // 'livre' ou 'total'
                            $letra = $matches[2]; // 'C:', etc.
                            if (!isset($discos_agrupados[$letra])) {
                                $discos_agrupados[$letra] = [
                                    'letra' => $letra,
                                    'total' => null,
                                    'livre' => null
                                ];
                            }
                            $discos_agrupados[$letra][$tipo_campo] = (float)$rd['valor'];
                        } else {
                            $discos_agrupados[$rd['nome']] = [
                                'letra' => $rd['nome'],
                                'total' => null,
                                'livre' => (float)$rd['valor']
                            ];
                        }
                    }

                    $discos = [];
                    foreach ($discos_agrupados as $key => $info) {
                        $total = $info['total'];
                        $livre = $info['livre'];
                        $letra = $info['letra'];

                        if ($total !== null && $total > 0 && $livre !== null) {
                            $usado = $total - $livre;
                            $porcentagem_uso = ($usado / $total) * 100;
                        } else {
                            $usado = 0;
                            $porcentagem_uso = 0;
                        }

                        $discos[] = [
                            'letra' => $letra,
                            'nome' => (strpos($letra, 'Disco') === 0) ? $letra : "Disco ($letra)",
                            'total' => $total,
                            'livre' => $livre,
                            'usado' => $usado,
                            'uso_porcentagem' => min(100, max(0, $porcentagem_uso))
                        ];
                    }
                }
            }
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/server/details.php';
        require 'app/views/layout/footer.php';
    }
}
