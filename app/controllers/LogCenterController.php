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
            // Seeder automático de logs de teste cobrindo todas as 6 categorias solicitadas
            try {
                $stmtCountLogs = $db->query("SELECT COUNT(*) FROM logs");
                $logsCount = $stmtCountLogs ? (int)$stmtCountLogs->fetchColumn() : 0;
                if ($logsCount < 10) {
                    // Limpar logs antigos para inicializar a demonstração completa
                    $db->exec("DELETE FROM logs");
                    
                    // 1. Logs de Sistema Operacional (Syslogs)
                    $db->exec("INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) VALUES 
                        (null, 'Syslog - Evento de Hardware', 'Alerta de superaquecimento no processador central (Core #3 atingiu 92°C)', '127.0.0.1'),
                        (null, 'Syslog - Alteração de Estado', 'Servidor de Produção SRV-BD01 reinicializado com sucesso após atualização agendada', '127.0.0.1'),
                        (null, 'Syslog - Serviço de Infraestrutura', 'Falha crítica no serviço DNS do Active Directory (serviço parou de responder)', '127.0.0.1')");

                    // 2. Logs de Aplicação
                    $db->exec("INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) VALUES 
                        (null, 'Erro de Aplicação', 'Exceção NullReferenceException no fluxo de finalização de compras (API V2)', '127.0.0.1'),
                        (null, 'Acesso de Aplicação', 'Requisição HTTP POST /api/v1/checkout - Status 500 (Erro Interno do Servidor)', '192.168.10.15'),
                        (null, 'Fluxo de Operação', 'Inicialização bem-sucedida do serviço worker de envio de e-mails em lote', '127.0.0.1')");

                    // 3. Logs de Segurança e Auditoria
                    $db->exec("INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) VALUES 
                        (null, 'Segurança - Autenticação', 'Tentativa de login malsucedida para o usuário root - IP 185.220.101.4 (Alerta de Força Bruta)', '185.220.101.4'),
                        (1, 'Segurança - Privilégios', 'Elevação de privilégios para o usuário suporte_tecnico ao grupo Administradores', '192.168.1.50'),
                        (null, 'Segurança - Ameaça', 'Ação de malware bloqueada pelo Firewall de Endpoint local (Trojan.Win32.Generic)', '127.0.0.1')");

                    // 4. Logs de Rede
                    $db->exec("INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) VALUES 
                        (null, 'Rede - Conexão', 'Conexão bloqueada na porta TCP 445 (SMB) a partir do IP externo 45.138.2.14', '45.138.2.14'),
                        (null, 'Rede - Desempenho', 'Queda de pacotes excessiva detectada na interface WAN (12.4% de perda de pacotes no gateway)', '127.0.0.1'),
                        (null, 'Rede - Desempenho', 'Instabilidade de rotas detectada: oscilação na sessão BGP primária com o provedor de trânsito', '127.0.0.1')");

                    // 5. Logs de Servidor Web (Web Servers)
                    $db->exec("INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) VALUES 
                        (null, 'Servidor Web - Tráfego', 'Origem geográfica detectada incomum para o endpoint /admin de IP localizado fora da faixa padrão', '93.184.216.34'),
                        (null, 'Servidor Web - Erro', 'Requisição malformada HTTP/1.1 (Erro 400 Bad Request) em lote detectada no log do Apache', '127.0.0.1')");

                    // 6. Logs de Serviços Específicos
                    $db->exec("INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) VALUES 
                        (null, 'Banco de Dados', 'Consulta SQL lenta detectada (tempo de execução: 8.42s) - SELECT * FROM transacoes_historico', '127.0.0.1'),
                        (null, 'Sistema de Backup', 'Rotina de cópia de segurança concluída com SUCESSO - Volume Backup_Infra_Prod', '127.0.0.1'),
                        (null, 'Sistema de Backup', 'Rotina de cópia de segurança FALHOU - Sem espaço disponível no storage NAS secundário', '127.0.0.1')");
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
