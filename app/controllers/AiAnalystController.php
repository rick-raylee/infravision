<?php

class AiAnalystController {

    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        require 'app/views/layout/header.php';
        require 'app/views/ai-analyst/index.php';
        require 'app/views/layout/footer.php';
    }

    public function chat() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (!headers_sent()) {
                http_response_code(405);
            }
            echo json_encode(["status" => "erro", "mensagem" => "Método não permitido. Utilize POST."]);
            exit;
        }

        $input = $this->getInputData();
        $mensagem_usuario = $input['mensagem'] ?? '';
        $tipo_analise = $input['tipo_analise'] ?? 'chat';

        if (empty($mensagem_usuario) && $tipo_analise === 'chat') {
            echo json_encode(["status" => "erro", "mensagem" => "A mensagem não pode estar vazia."]);
            exit;
        }

        require_once 'config/database.php';
        $db = (new Database())->getConnection();

        if (!$db) {
            echo json_encode(["status" => "erro", "mensagem" => "Não foi possível conectar ao banco de dados para buscar contexto."]);
            exit;
        }

        $prompt_final = $mensagem_usuario;

        // Se for uma análise automática ou assistida por contexto
        if ($tipo_analise === 'logs') {
            try {
                $queryLogs = "SELECT l.acao, l.detalhes, l.ip_origem, l.criado_em, u.nome AS usuario 
                              FROM logs l 
                              LEFT JOIN usuarios u ON u.id = l.usuario_id 
                              ORDER BY l.criado_em DESC LIMIT 5";
                $stmtLogs = $db->prepare($queryLogs);
                $stmtLogs->execute();
                $recent_logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

                $queryAlerts = "SELECT a.mensagem, a.severidade, a.status, a.criado_em, d.nome AS dispositivo
                                FROM alertas a
                                LEFT JOIN dispositivos d ON d.id = a.dispositivo_id
                                ORDER BY a.criado_em DESC LIMIT 5";
                $stmtAlerts = $db->prepare($queryAlerts);
                $stmtAlerts->execute();
                $recent_alerts = $stmtAlerts->fetchAll(PDO::FETCH_ASSOC);

                $contexto = "--- CONTEXTO DO NOC - LOGS E ALERTAS RECENTES ---\n\n";
                $contexto .= "LOGS RECENTES:\n";
                if (empty($recent_logs)) {
                    $contexto .= "(Nenhum log registrado recentemente)\n";
                } else {
                    foreach ($recent_logs as $l) {
                        $usr = $l['usuario'] ?? $l['ip_origem'] ?? 'Sistema';
                        $contexto .= "- [{$l['criado_em']}] [{$usr}] {$l['acao']}" . ($l['detalhes'] ? " - {$l['detalhes']}" : "") . "\n";
                    }
                }

                $contexto .= "\nALERTAS RECENTES:\n";
                if (empty($recent_alerts)) {
                    $contexto .= "(Nenhum alerta crítico ativo/recente)\n";
                } else {
                    foreach ($recent_alerts as $a) {
                        $dev = $a['dispositivo'] ?? 'Sistema';
                        $contexto .= "- [{$a['criado_em']}] [{$dev}] [Severidade: {$a['severidade']}] [Status: {$a['status']}] {$a['mensagem']}\n";
                    }
                }

                $prompt_final = $contexto . "\nInstrução do Operador: " . ($mensagem_usuario ?: "Por favor, analise a central de logs e alertas acima. Correlacione os eventos, identifique as causas raízes de falhas, aponte potenciais riscos de segurança ou operacionais e dê recomendações de correção.");
            } catch (Exception $e) {
                $prompt_final = "Erro ao buscar logs: " . $e->getMessage() . "\n" . $mensagem_usuario;
            }
        } elseif ($tipo_analise === 'dispositivos') {
            try {
                require_once 'app/models/Device.php';
                $deviceModel = new Device($db);
                $devices = $deviceModel->getAllWithMetrics();

                $contexto = "--- CONTEXTO DO NOC - STATUS E MÉTRICAS DOS DISPOSITIVOS ---\n\n";
                if (empty($devices)) {
                    $contexto .= "(Nenhum dispositivo cadastrado no inventário)\n";
                } else {
                    foreach ($devices as $d) {
                        $cpu = $d['cpu_atual'] !== null ? round($d['cpu_atual'], 1) . '%' : 'N/D';
                        $ram_livre = $d['ram_livre'] !== null ? round($d['ram_livre']) . ' MB' : 'N/D';
                        $ram_total = $d['ram_total'] !== null ? round($d['ram_total']) . ' MB' : 'N/D';
                        $ram_uso = $d['ram_atual'] !== null ? round($d['ram_atual'], 1) . '%' : 'N/D';
                        
                        $contexto .= "- Hostname: {$d['hostname']} (IP: {$d['ip']}, Tipo: {$d['tipo']})\n";
                        $contexto .= "  Status: {$d['status']}, Último Check-in: {$d['ultimo_check']}\n";
                        $contexto .= "  Uso de CPU: {$cpu}, RAM Livre: {$ram_livre} / Total: {$ram_total} (Uso: {$ram_uso})\n";
                    }
                }

                $prompt_final = $contexto . "\nInstrução do Operador: " . ($mensagem_usuario ?: "Por favor, examine o status de saúde dos servidores e dispositivos de rede acima. Identifique gargalos de recursos (como alto uso de CPU ou esgotamento de RAM), dispositivos inativos (offline/alerta) e proponha ações corretivas ou preventivas.");
            } catch (Exception $e) {
                $prompt_final = "Erro ao buscar dispositivos: " . $e->getMessage() . "\n" . $mensagem_usuario;
            }
        }

        $modelo_ativo = $input['modelo'] ?? 'gemma2:2b';
        $is_gemma4 = (strpos(strtolower($modelo_ativo), 'gemma4') !== false);
        $system_content = "Você é o AI Analyst, o assistente inteligente integrado ao painel de controle do InfraVision NOC. Você ajuda operadores de infraestrutura de TI a monitorá-lo, diagnosticar e resolver problemas de hardware, redes, servidores e segurança. Suas respostas devem ser precisas, altamente técnicas, objetivas e formatadas claramente em Markdown em português (PT-BR). Se houver códigos, logs ou passos de comando, formate-os em blocos de código adequados.";
        if ($is_gemma4) {
            $system_content .= " Limite seu pensamento (thinking) ao mínimo necessário para ser breve e vá direto ao ponto nas explicações.";
        }

        echo json_encode([
            "status" => "sucesso",
            "system_prompt" => $system_content,
            "user_prompt" => $prompt_final
        ], JSON_UNESCAPED_UNICODE);
    }

    protected function getInputData() {
        return json_decode(file_get_contents('php://input'), true);
    }
}
