<?php

class LogCenterController {
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');

        require_once 'config/database.php';
        $eventos = [];
        $servidores = [];

        $db = (new Database())->getConnection();
        if ($db) {
            $queryAlertas = "SELECT a.id, a.mensagem, a.severidade, a.status, a.criado_em,
                                    d.nome AS servidor
                             FROM alertas a
                             LEFT JOIN dispositivos d ON d.id = a.dispositivo_id
                             ORDER BY a.criado_em DESC
                             LIMIT 100";
            $stmt = $db->prepare($queryAlertas);
            $stmt->execute();
            $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $queryLogs = "SELECT l.id, l.acao AS mensagem, l.detalhes, l.ip_origem, l.criado_em,
                                 u.nome AS usuario
                          FROM logs l
                          LEFT JOIN usuarios u ON u.id = l.usuario_id
                          ORDER BY l.criado_em DESC
                          LIMIT 100";
            try {
                $stmtLogs = $db->prepare($queryLogs);
                $stmtLogs->execute();
                foreach ($stmtLogs->fetchAll(PDO::FETCH_ASSOC) as $log) {
                    $eventos[] = [
                        'id' => 'log-' . $log['id'],
                        'mensagem' => $log['mensagem'] . ($log['detalhes'] ? ' — ' . $log['detalhes'] : ''),
                        'severidade' => 'info',
                        'status' => 'registrado',
                        'criado_em' => $log['criado_em'],
                        'servidor' => $log['usuario'] ?? $log['ip_origem'] ?? 'Sistema',
                    ];
                }
                usort($eventos, fn($a, $b) => strtotime($b['criado_em']) <=> strtotime($a['criado_em']));
                $eventos = array_slice($eventos, 0, 100);
            } catch (PDOException $e) {
                // tabela logs pode não existir em instalações antigas
            }

            $stmtSrv = $db->query("SELECT DISTINCT nome FROM dispositivos ORDER BY nome");
            $servidores = $stmtSrv->fetchAll(PDO::FETCH_COLUMN);
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/logcenter/index.php';
        require 'app/views/layout/footer.php';
    }
}
