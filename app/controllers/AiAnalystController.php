<?php

class AiAnalystController {

    private function getCandidateModels() {
        $ollama_url = getenv('OLLAMA_API_URL') ?: 'http://localhost:11434';
        $ch_models = curl_init(rtrim($ollama_url, '/') . '/api/tags');
        curl_setopt($ch_models, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_models, CURLOPT_TIMEOUT, 3);
        $models_response = curl_exec($ch_models);
        curl_close($ch_models);

        $installed_models = [];
        if ($models_response) {
            $models_data = json_decode($models_response, true);
            if (isset($models_data['models'])) {
                $installed_models = array_column($models_data['models'], 'name');
            }
        }

        // Selecionar o melhor modelo primário (prioridade para Gemma 4)
        $primary = null;
        $configured_model = getenv('OLLAMA_MODEL');
        if ($configured_model && in_array($configured_model, $installed_models)) {
            $primary = $configured_model;
        } elseif (in_array('gemma4:e2b', $installed_models)) {
            $primary = 'gemma4:e2b';
        } elseif (in_array('gemma4:latest', $installed_models)) {
            $primary = 'gemma4:latest';
        } elseif (in_array('gemma4:e4b', $installed_models)) {
            $primary = 'gemma4:e4b';
        }

        // Fallback leve de garantia
        $fallback = in_array('gemma2:2b', $installed_models) ? 'gemma2:2b' : null;

        $candidates = [];
        if ($primary) {
            $candidates[] = $primary;
        }
        if ($fallback && $fallback !== $primary) {
            $candidates[] = $fallback;
        }

        // Se a lista ainda estiver vazia, tentar usar o configurado ou o fallback como padrão
        if (empty($candidates)) {
            if ($configured_model) {
                $candidates[] = $configured_model;
            } else {
                $candidates[] = 'gemma2:2b';
            }
        }

        return $candidates;
    }

    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        $current_path = '/ai-analyst';

        // Detectar o modelo disponível para exibição na view
        $candidates = $this->getCandidateModels();
        $modelo_ativo = $candidates[0];
        $modelo_exibicao = 'Gemma 4 (Local)';
        if ($modelo_ativo === 'gemma2:2b') {
            $modelo_exibicao = 'Gemma 2 2B (Leve)';
        } elseif ($modelo_ativo === 'gemma4:e2b') {
            $modelo_exibicao = 'Gemma 4 E2B (Leve)';
        } elseif (strpos($modelo_ativo, 'gemma4') !== false) {
            $modelo_exibicao = 'Gemma 4 (Local)';
        } else {
            $modelo_exibicao = ucfirst($modelo_ativo);
        }

        require 'app/views/layout/header.php';
        require 'app/views/ai-analyst/index.php';
        require 'app/views/layout/footer.php';
    }

    public function chat() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        // Garantir que a requisição seja POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (!headers_sent()) {
                http_response_code(405);
            }
            echo json_encode(["status" => "erro", "mensagem" => "Método não permitido. Utilize POST."]);
            exit;
        }

        // Permitir tempo de execução estendido para processamento local do LLM
        set_time_limit(240);

        // Obter corpo da requisição JSON
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
                // Buscar logs recentes
                $queryLogs = "SELECT l.acao, l.detalhes, l.ip_origem, l.criado_em, u.nome AS usuario 
                              FROM logs l 
                              LEFT JOIN usuarios u ON u.id = l.usuario_id 
                              ORDER BY l.criado_em DESC LIMIT 5";
                $stmtLogs = $db->prepare($queryLogs);
                $stmtLogs->execute();
                $recent_logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

                // Buscar alertas recentes
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

        $modelo_solicitado = $input['modelo'] ?? null;

        // Detectar modelos candidatos ordenados por preferência
        $candidates = $this->getCandidateModels();
        if ($modelo_solicitado && in_array($modelo_solicitado, $candidates)) {
            $candidates = array_diff($candidates, [$modelo_solicitado]);
            array_unshift($candidates, $modelo_solicitado);
        }
        $response_data = null;
        $success = false;
        $error_message = '';
        $model_used = '';

        foreach ($candidates as $modelo_ativo) {
            $model_used = $modelo_ativo;

            // Determinar dinamicamente o número de tokens e timeout por modelo
            $is_gemma4 = (strpos($modelo_ativo, 'gemma4') !== false);
            $num_predict = $is_gemma4 ? 1000 : 350;
            $timeout = $is_gemma4 ? 125 : 90;

            $system_content = "Você é o AI Analyst, o assistente inteligente integrado ao painel de controle do InfraVision NOC. Você ajuda operadores de infraestrutura de TI a monitorá-lo, diagnosticar e resolver problemas de hardware, redes, servidores e segurança. Suas respostas devem ser precisas, altamente técnicas, objetivas e formatadas claramente em Markdown em português (PT-BR). Se houver códigos, logs ou passos de comando, formate-os em blocos de código adequados.";
            if ($is_gemma4) {
                $system_content .= " Limite seu pensamento (thinking) ao mínimo necessário para ser breve e vá direto ao ponto nas explicações.";
            }

            // Comunicação com a API do Ollama Local
            $ollama_url = getenv('OLLAMA_API_URL') ?: 'http://localhost:11434';
            $url = rtrim($ollama_url, '/') . '/api/chat';
            $payload = [
                "model" => $modelo_ativo,
                "messages" => [
                    [
                        "role" => "system",
                        "content" => $system_content
                    ],
                    [
                        "role" => "user",
                        "content" => $prompt_final
                    ]
                ],
                "options" => [
                    "num_predict" => $num_predict,
                    "temperature" => 0.2
                ],
                "stream" => false
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                $error_message = "Não foi possível conectar ao serviço Ollama local para o modelo '{$modelo_ativo}'. Erro: {$curl_error}";
                continue; // Tenta o próximo modelo
            }

            if ($http_code !== 200) {
                $err_decoded = json_decode($response, true);
                $err_msg = $err_decoded['error'] ?? "O Ollama retornou um código de status HTTP inválido: {$http_code}.";

                // Se for falta de memória, tenta o próximo candidato
                if (stripos($err_msg, 'memory') !== false || stripos($err_msg, 'system memory') !== false) {
                    $error_message = "O modelo '{$modelo_ativo}' falhou devido a restrições de memória do sistema: {$err_msg}";
                    continue;
                }

                $error_message = $err_msg;
                continue;
            }

            $res_dec = json_decode($response, true);
            $resposta_modelo = $res_dec['message']['content'] ?? '';

            if (empty($resposta_modelo)) {
                $error_message = "O modelo '{$modelo_ativo}' respondeu com um conteúdo vazio.";
                continue;
            }

            // Se chegamos aqui, o processamento foi concluído com sucesso
            $response_data = $resposta_modelo;
            $success = true;
            break;
        }

        if (!$success) {
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Não foi possível obter resposta de nenhum modelo LLM disponível. Último erro: " . $error_message
            ]);
            exit;
        }

        // Definir exibição legível do modelo usado para o front-end saber qual respondeu
        $modelo_exibicao_resposta = 'Gemma 4 (Local)';
        if ($model_used === 'gemma2:2b') {
            $modelo_exibicao_resposta = 'Gemma 2 2B (Leve)';
        } elseif ($model_used === 'gemma4:e2b') {
            $modelo_exibicao_resposta = 'Gemma 4 E2B (Leve)';
        } elseif (strpos($model_used, 'gemma4') !== false) {
            $modelo_exibicao_resposta = 'Gemma 4 (Local)';
        } else {
            $modelo_exibicao_resposta = ucfirst($model_used);
        }

        echo json_encode([
            "status" => "sucesso",
            "resposta" => $response_data,
            "modelo_usado" => $modelo_exibicao_resposta
        ], JSON_UNESCAPED_UNICODE);
    }

    protected function getInputData() {
        return json_decode(file_get_contents('php://input'), true);
    }
}
