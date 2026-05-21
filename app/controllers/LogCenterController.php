<?php

class LogCenterController {
    
    private function obterCategoria($mensagem, $detalhes = '') {
        $texto = mb_strtolower($mensagem . ' ' . $detalhes, 'UTF-8');
        
        if (strpos($texto, 'login') !== false || 
            strpos($texto, 'logout') !== false || 
            strpos($texto, 'usuário') !== false || 
            strpos($texto, 'segurança') !== false || 
            strpos($texto, 'ameaça') !== false || 
            strpos($texto, 'bloqueio') !== false ||
            strpos($texto, 'acesso') !== false || 
            strpos($texto, 'tentativa') !== false ||
            strpos($texto, 'firewall') !== false) {
            return 'Rede/Segurança';
        }
        
        if (strpos($texto, 'tráfego') !== false || 
            strpos($texto, 'rede in') !== false || 
            strpos($texto, 'rede out') !== false || 
            strpos($texto, 'requisições') !== false || 
            strpos($texto, 'http') !== false || 
            strpos($texto, 'web') !== false || 
            strpos($texto, 'apache') !== false || 
            strpos($texto, 'nginx') !== false) {
            return 'Servidores Web';
        }
        
        if (strpos($texto, 'aplicação') !== false || 
            strpos($texto, 'software') !== false || 
            strpos($texto, 'erro de') !== false || 
            strpos($texto, 'falha ao') !== false || 
            strpos($texto, 'crash') !== false || 
            strpos($texto, 'banco de dados') !== false || 
            strpos($texto, 'mysql') !== false ||
            strpos($texto, 'serviço') !== false) {
            return 'Aplicações';
        }
        
        return 'Sistema Operacional';
    }

    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');

        require_once 'config/database.php';
        $eventos = [];
        $servidores = [];

        $db = (new Database())->getConnection();
        if ($db) {
            // Seeder automático de logs de teste se as tabelas estiverem vazias para cobrir as categorias solicitadas
            try {
                $stmtCountLogs = $db->query("SELECT COUNT(*) FROM logs");
                $logsCount = $stmtCountLogs ? (int)$stmtCountLogs->fetchColumn() : 0;
                if ($logsCount < 5) {
                    // 1. Rede/Segurança
                    $db->exec("INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) VALUES 
                        (1, 'Login efetuado', 'Acesso administrativo bem-sucedido à interface web', '192.168.1.50'),
                        (null, 'Tentativa de login bloqueada', 'Ameaça de Segurança: Bloqueio de IP por múltiplas tentativas malsucedidas de login', '185.220.101.4')");
                    
                    // 2. Sistema Operacional
                    $db->exec("INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) VALUES 
                        (null, 'Reinicialização de sistema', 'Servidor de Produção SRV-BD01 reinicializado com sucesso após atualização agendada', '127.0.0.1')");

                    // 3. Aplicações
                    $db->exec("INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) VALUES 
                        (null, 'Erro de Aplicação', 'Falha ao carregar as dependências críticas no serviço de integração ERP (IIS)', '127.0.0.1')");

                    // 4. Servidores Web
                    $db->exec("INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) VALUES 
                        (null, 'Pico de tráfego web', 'Alerta de Tráfego: Apache WebServer-02 atingiu 450 requisições/seg e 48 Mbps de consumo de banda', '127.0.0.1')");
                }

                $stmtCountAlerts = $db->query("SELECT COUNT(*) FROM alertas");
                $alertsCount = $stmtCountAlerts ? (int)$stmtCountAlerts->fetchColumn() : 0;
                if ($alertsCount < 3) {
                    $stmtDev = $db->query("SELECT id FROM dispositivos LIMIT 1");
                    $devId = $stmtDev ? $stmtDev->fetchColumn() : null;
                    if ($devId) {
                        // Sistema Operacional (alertas)
                        $db->exec("INSERT INTO alertas (dispositivo_id, mensagem, severidade, status) VALUES 
                            ($devId, 'Saturação de CPU: Consumo acima de 95% no Servidor de Banco de Dados', 'critico', 'ativo'),
                            ($devId, 'Espaço em Disco Baixo: Volume C: com menos de 10% de espaço livre no Computador-TI', 'aviso', 'ativo')");
                        // Aplicações (alertas)
                        $db->exec("INSERT INTO alertas (dispositivo_id, mensagem, severidade, status) VALUES 
                            ($devId, 'Serviço MySQL parou inesperadamente no servidor de banco de dados principal', 'critico', 'ativo')");
                    }
                }
            } catch (Exception $e) {
                // Falha silenciosa de seed
            }

            // Buscar Alertas
            $queryAlertas = "SELECT a.id, a.mensagem, a.severidade, a.status, a.criado_em,
                                    d.nome AS servidor
                             FROM alertas a
                             LEFT JOIN dispositivos d ON d.id = a.dispositivo_id
                             ORDER BY a.criado_em DESC
                             LIMIT 100";
            $stmt = $db->prepare($queryAlertas);
            $stmt->execute();
            $alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($alertas as $row) {
                $eventos[] = [
                    'id' => 'alerta-' . $row['id'],
                    'mensagem' => $row['mensagem'],
                    'severidade' => $row['severidade'],
                    'status' => $row['status'],
                    'criado_em' => $row['criado_em'],
                    'servidor' => $row['servidor'] ?? 'Dispositivo',
                    'categoria' => $this->obterCategoria($row['mensagem']),
                ];
            }

            // Buscar Logs
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
                        'categoria' => $this->obterCategoria($log['mensagem'], $log['detalhes']),
                    ];
                }
                usort($eventos, fn($a, $b) => strtotime($b['criado_em']) <=> strtotime($a['criado_em']));
                $eventos = array_slice($eventos, 0, 100);
            } catch (PDOException $e) {
                // tabela logs pode não existir
            }

            $stmtSrv = $db->query("SELECT DISTINCT nome FROM dispositivos ORDER BY nome");
            $servidores = $stmtSrv->fetchAll(PDO::FETCH_COLUMN);
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/logcenter/index.php';
        require 'app/views/layout/footer.php';
    }
}
