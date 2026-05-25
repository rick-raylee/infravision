<?php

class LogCenterController {
    
    private function obterCategoria($mensagem, $detalhes = '') {
        $texto = mb_strtolower($mensagem . ' ' . $detalhes, 'UTF-8');
        
        // 6. Serviços Específicos
        if (strpos($texto, 'banco de dados') !== false || 
            strpos($texto, 'sql') !== false || 
            strpos($texto, 'mysql') !== false || 
            strpos($texto, 'postgres') !== false || 
            strpos($texto, 'backup') !== false || 
            strpos($texto, 'cópia de segurança') !== false ||
            strpos($texto, 'consulta lenta') !== false) {
            return 'Serviços Específicos';
        }
        
        // 3. Segurança e Auditoria
        if (strpos($texto, 'login') !== false || 
            strpos($texto, 'logout') !== false || 
            strpos($texto, 'usuário') !== false || 
            strpos($texto, 'segurança') !== false || 
            strpos($texto, 'ameaça') !== false || 
            strpos($texto, 'bloqueio') !== false ||
            strpos($texto, 'autenticação') !== false ||
            strpos($texto, 'privilégios') !== false ||
            strpos($texto, 'permissões') !== false ||
            strpos($texto, 'intrusão') !== false ||
            strpos($texto, 'antivírus') !== false ||
            strpos($texto, 'brute force') !== false ||
            strpos($texto, 'força bruta') !== false ||
            strpos($texto, 'malware') !== false) {
            return 'Segurança e Auditoria';
        }

        // 4. Rede
        if (strpos($texto, 'tráfego de rede') !== false || 
            strpos($texto, 'latência') !== false || 
            strpos($texto, 'pacotes') !== false || 
            strpos($texto, 'ping') !== false ||
            strpos($texto, 'banda') !== false || 
            strpos($texto, 'switch') !== false || 
            strpos($texto, 'roteador') !== false || 
            strpos($texto, 'conexão de rede') !== false ||
            strpos($texto, 'porta tcp') !== false ||
            strpos($texto, 'rotas') !== false ||
            strpos($texto, 'rede -') !== false ||
            strpos($texto, 'firewall') !== false) {
            return 'Rede';
        }

        // 5. Servidor Web
        if (strpos($texto, 'servidor web') !== false || 
            strpos($texto, 'web server') !== false || 
            strpos($texto, 'nginx') !== false || 
            strpos($texto, 'iis') !== false ||
            strpos($texto, 'apache') !== false ||
            strpos($texto, 'tráfego -') !== false ||
            strpos($texto, 'origem geográfica') !== false ||
            strpos($texto, 'requisição malformada') !== false ||
            strpos($texto, 'bad request') !== false) {
            return 'Servidor Web';
        }

        // 2. Aplicação
        if (strpos($texto, 'aplicação') !== false || 
            strpos($texto, 'software') !== false || 
            strpos($texto, 'erro de software') !== false || 
            strpos($texto, 'exceção') !== false || 
            strpos($texto, 'crash') !== false || 
            strpos($texto, 'runtime') !== false ||
            strpos($texto, 'tempo de execução') !== false ||
            strpos($texto, 'requisição http') !== false ||
            strpos($texto, 'http/https') !== false ||
            strpos($texto, 'fluxo de operação') !== false ||
            strpos($texto, 'worker') !== false ||
            strpos($texto, 'status 500') !== false ||
            strpos($texto, 'status 404') !== false ||
            strpos($texto, 'status 200') !== false) {
            return 'Aplicação';
        }

        // 1. Sistema Operacional (Syslogs, Hardware events, cpu/ram/disk limits, shutdown, reboots, infrastructure services: DNS, DHCP, Active Directory)
        return 'Sistema Operacional';
    }

    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');

        require_once 'config/database.php';
        $eventos = [];
        $servidores = [];

        $db = (new Database())->getConnection();
        if ($db) {
            // O seeder automático foi removido por razões de segurança.
            // Os logs agora refletem estritamente os eventos reais do banco de dados.

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
                    $textoCompleto = mb_strtolower($log['mensagem'] . ' ' . $log['detalhes'], 'UTF-8');
                    
                    // Identificação dinâmica e inteligente da severidade para exibição realista
                    $sev = 'info';
                    if (strpos($textoCompleto, 'falhou') !== false || 
                        strpos($textoCompleto, 'falha crítica') !== false || 
                        strpos($textoCompleto, 'erro de') !== false || 
                        strpos($textoCompleto, 'bloqueada') !== false || 
                        strpos($textoCompleto, 'bloqueado') !== false) {
                        $sev = 'critico';
                    } elseif (strpos($textoCompleto, 'alerta') !== false || 
                              strpos($textoCompleto, 'malsucedida') !== false || 
                              strpos($textoCompleto, 'instabilidade') !== false || 
                              strpos($textoCompleto, 'lenta') !== false) {
                        $sev = 'aviso';
                    }

                    $eventos[] = [
                        'id' => 'log-' . $log['id'],
                        'mensagem' => $log['mensagem'] . ($log['detalhes'] ? ' — ' . $log['detalhes'] : ''),
                        'severidade' => $sev,
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
