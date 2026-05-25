<?php
// Arquivo: api/receber_agente.php
// Objetivo: Receber dados de agentes locais instalados nos servidores (Zabbix Active Agent style)

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

// Instanciar o Database para carregar as variáveis do .env
$db = (new Database())->getConnection();

// CORREÇÃO: Autenticação via Token Obrigatória
$expectedToken = getenv('AGENT_API_TOKEN') ?: 'infravision_default_secure_token';
$authHeader = '';

if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? '';
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
}

if (strpos($authHeader, 'Bearer ') !== 0 || substr($authHeader, 7) !== $expectedToken) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado. Token inválido.']);
    exit;
}

// Ler o JSON enviado pelo agente (via POST ou CLI stdin)
if (php_sapi_name() === 'cli') {
    $dados_json = file_get_contents("php://stdin");
} else {
    $dados_json = file_get_contents("php://input");
}
$dados = json_decode($dados_json, true);

if (!$dados) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos ou JSON malformado']);
    exit;
}

// Extrair dados do Agente
$hostname = $dados['hostname'] ?? 'Desconhecido';
$ip = $dados['ip'] ?? '0.0.0.0';
$cpu = $dados['cpu_load'] ?? 0;
$mem_livre = $dados['ram_livre_mb'] ?? 0;
$mem_total = $dados['ram_total_mb'] ?? 0;
$discos = $dados['discos'] ?? [];
$servicos = $dados['servicos'] ?? [];
$conexoes = $dados['conexoes'] ?? [];
$nobreak = $dados['nobreak'] ?? null;

$tipo = $dados['tipo'] ?? 'servidor_windows';
$usuario_logado = $dados['usuario_logado'] ?? null;
$fabricante = $dados['fabricante'] ?? null;
$modelo = $dados['modelo'] ?? null;
$numero_serie = $dados['numero_serie'] ?? null;
$sistema_operacional = $dados['sistema_operacional'] ?? null;
$processador = $dados['processador'] ?? null;

/**
 * Win32_Battery.EstimatedRunTime retorna códigos especiais quando desconhecido (ex.: 65535 ou ~71582788 na tomada).
 */
function normalizarAutonomiaNobreak($valor): ?float {
    if ($valor === null || $valor === '') {
        return null;
    }
    $minutos = (float)$valor;
    if ($minutos <= 0 || $minutos >= 65535 || $minutos >= 71582700 || $minutos > 10080) {
        return null;
    }
    return $minutos;
}

function normalizarNomeNobreak(?string $nome, string $hostname): string {
    $nome = trim((string)$nome);
    if ($nome === '' || preg_match('/^\d+$/', $nome)) {
        return 'Nobreak USB - ' . $hostname;
    }
    return $nome;
}

$db = (new Database())->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro de conexão com o banco de dados']);
    exit;
}

try {
    // 1. Verificar se dispositivo existe (pelo IP ou Hostname)
    $stmt = $db->prepare("SELECT id FROM dispositivos WHERE ip = ? OR nome = ? LIMIT 1");
    $stmt->execute([$ip, $hostname]);
    $dispositivo = $stmt->fetch();
    
    $dispositivo_id = null;

    if ($dispositivo) {
        $dispositivo_id = $dispositivo['id'];
        // Atualizar status, tipo e ficha técnica
        $stmt = $db->prepare("UPDATE dispositivos SET 
            status = 'online', 
            tipo = ?,
            usuario_logado = ?,
            fabricante = ?,
            modelo = ?,
            numero_serie = ?,
            sistema_operacional = ?,
            processador = ?,
            ultimo_check = NOW() 
            WHERE id = ?");
        $stmt->execute([$tipo, $usuario_logado, $fabricante, $modelo, $numero_serie, $sistema_operacional, $processador, $dispositivo_id]);
    } else {
        // 2. Auto-discovery: Cadastrar dispositivo
        $stmt = $db->prepare("INSERT INTO dispositivos (nome, ip, tipo, status, usuario_logado, fabricante, modelo, numero_serie, sistema_operacional, processador, ultimo_check) VALUES (?, ?, ?, 'online', ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$hostname, $ip, $tipo, $usuario_logado, $fabricante, $modelo, $numero_serie, $sistema_operacional, $processador]);
        $dispositivo_id = $db->lastInsertId();
        
        // Gravar log de auditoria
        require_once __DIR__ . '/../app/models/AuditLog.php';
        AuditLog::write($db, null, 'Dispositivo auto-descoberto', "Nome: $hostname, IP: $ip, Tipo: $tipo");
    }

    // Função auxiliar para buscar ou criar sensor
    $getOrCreateSensor = function($db, $dispositivo_id, $nome, $tipo) {
        $stmt = $db->prepare("SELECT id FROM sensores WHERE dispositivo_id = ? AND nome = ? AND tipo = ? LIMIT 1");
        $stmt->execute([$dispositivo_id, $nome, $tipo]);
        $sensor = $stmt->fetch();
        if ($sensor) {
            return $sensor['id'];
        } else {
            $stmt = $db->prepare("INSERT INTO sensores (dispositivo_id, nome, tipo, ativo) VALUES (?, ?, ?, 1)");
            $stmt->execute([$dispositivo_id, $nome, $tipo]);
            return $db->lastInsertId();
        }
    };

    // --- ISP Lookup based on Agent's Public IP ---
    $public_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    if ($public_ip) {
        $public_ip = trim(explode(',', $public_ip)[0]); // Tratar múltiplos IPs (proxy)
        
        // Se for IP local ou reservado, limpa a variável para forçar a API a pegar o IP público do servidor
        if (filter_var($public_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            $public_ip = '';
        }

        $isp_cache_file = sys_get_temp_dir() . '/infravision_isp_' . $dispositivo_id . '.txt';
        if (!file_exists($isp_cache_file) || (time() - filemtime($isp_cache_file)) > 86400) {
            $apiUrl = 'http://ip-api.com/json/' . $public_ip;
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $res = curl_exec($ch);
            curl_close($ch);
            
            if ($res) {
                $json = json_decode($res, true);
                if (isset($json['isp']) && !empty($json['isp'])) {
                    file_put_contents($isp_cache_file, $json['isp']);
                }
            }
        }
    }
    // ---------------------------------------------

    // 3. Preparar sensores para salvar
    $sensoresParaSalvar = [
        ['nome' => 'CPU Load',        'tipo' => 'cpu', 'valor' => $cpu],
        ['nome' => 'RAM Livre (MB)',   'tipo' => 'ram', 'valor' => $mem_livre],
        ['nome' => 'RAM Total (MB)',   'tipo' => 'ram', 'valor' => $mem_total]
    ];

    // Bandwidth de rede (enviado pelo agente C# v2+)
    if (isset($dados['rede_in_mbps'])) {
        $sensoresParaSalvar[] = ['nome' => 'Rede In (Mbps)',  'tipo' => 'rede_in',  'valor' => (float)$dados['rede_in_mbps']];
    }
    if (isset($dados['rede_out_mbps'])) {
        $sensoresParaSalvar[] = ['nome' => 'Rede Out (Mbps)', 'tipo' => 'rede_out', 'valor' => (float)$dados['rede_out_mbps']];
    }

    // Adicionar discos
    foreach ($discos as $disco) {
        $letra = $disco['letra'] ?? 'C:';
        $livre = $disco['livre_gb'] ?? 0;
        $tamanho = $disco['tamanho_gb'] ?? 0;
        $sensoresParaSalvar[] = ['nome' => "Disco Livre ($letra) GB", 'tipo' => 'disco', 'valor' => $livre];
        if ($tamanho > 0) {
            $sensoresParaSalvar[] = ['nome' => "Disco Total ($letra) GB", 'tipo' => 'disco', 'valor' => $tamanho];
        }
    }

    // Adicionar sensores do nobreak/bateria se existirem
    if (isset($dados['ups_bateria'])) {
        $sensoresParaSalvar[] = ['nome' => 'Capacidade da Bateria (%)', 'tipo' => 'bateria', 'valor' => $dados['ups_bateria']];
    }
    if (isset($dados['ups_autonomia'])) {
        $sensoresParaSalvar[] = ['nome' => 'Tempo de Autonomia (Minutos)', 'tipo' => 'uptime', 'valor' => $dados['ups_autonomia']];
    }
    if (isset($dados['ups_tensao'])) {
        $sensoresParaSalvar[] = ['nome' => 'Tensão de Entrada (V)', 'tipo' => 'tensao', 'valor' => $dados['ups_tensao']];
    }
    if (isset($dados['ups_carga'])) {
        $sensoresParaSalvar[] = ['nome' => 'Carga (%)', 'tipo' => 'carga_nobreak', 'valor' => $dados['ups_carga']];
    }

    // Processar sensores e inserir leituras
    $stmtInsert = $db->prepare("INSERT INTO leituras (sensor_id, valor, data_leitura) VALUES (?, ?, NOW())");
    
    foreach ($sensoresParaSalvar as $s) {
        $sensor_id = $getOrCreateSensor($db, $dispositivo_id, $s['nome'], $s['tipo']);
        $stmtInsert->execute([$sensor_id, $s['valor']]);
    }

    // 4. Salvar Conexões Ativas (Tráfego de Rede Real)
    $db->beginTransaction();
    try {
        $stmtDeleteConn = $db->prepare("DELETE FROM conexoes WHERE dispositivo_id = ?");
        $stmtDeleteConn->execute([$dispositivo_id]);

        if (!empty($conexoes)) {
            $stmtInsertConn = $db->prepare("INSERT INTO conexoes (dispositivo_id, origem, ip_origem, destino, servico, latencia, carga) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($conexoes as $conn_data) {
                $orig = $conn_data['origem'] ?? $hostname;
                $ip_orig = $conn_data['ip_origem'] ?? $ip;
                $dest = $conn_data['destino'] ?? 'Desconhecido';
                $serv = $conn_data['servico'] ?? 'N/A';
                $lat = isset($conn_data['latencia']) ? (int)$conn_data['latencia'] : 0;
                $carg = isset($conn_data['carga']) ? (int)$conn_data['carga'] : 0;
                
                $stmtInsertConn->execute([$dispositivo_id, $orig, $ip_orig, $dest, $serv, $lat, $carg]);
            }
        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

    // 5. Nobreak: somente se o agente estiver com monitor_nobreak=true E o dispositivo ja existir no painel
    $monitor_nobreak = !empty($dados['monitor_nobreak']);
    if ($nobreak && $monitor_nobreak) {
        $nb_nome = normalizarNomeNobreak($nobreak['nome'] ?? null, $hostname);
        $nb_ip = $nobreak['ip'] ?? $ip;
        $nb_bateria = isset($nobreak['bateria']) ? min(100, max(0, (float)$nobreak['bateria'])) : null;
        $nb_autonomia = normalizarAutonomiaNobreak($nobreak['autonomia'] ?? null);
        $nb_tensao = isset($nobreak['tensao']) ? (float)$nobreak['tensao'] : null;
        $nb_carga = isset($nobreak['carga']) ? min(100, max(0, (float)$nobreak['carga'])) : null;
        $nb_status = $nobreak['status'] ?? 'online';

        $stmtNB = $db->prepare("SELECT id FROM dispositivos WHERE tipo = 'nobreak' AND ip = ? LIMIT 1");
        $stmtNB->execute([$nb_ip]);
        $nb_dispositivo = $stmtNB->fetch();

        if ($nb_dispositivo) {
            $nb_id = $nb_dispositivo['id'];
            $stmtUpdateNB = $db->prepare("UPDATE dispositivos SET nome = ?, status = ?, ultimo_check = NOW() WHERE id = ?");
            $stmtUpdateNB->execute([$nb_nome, $nb_status, $nb_id]);
        } else {
            // Cria o nobreak automaticamente caso tenha sido detectado e validado pelo agente
            $stmtInsertNB = $db->prepare("INSERT INTO dispositivos (nome, ip, tipo, status, ultimo_check) VALUES (?, ?, 'nobreak', ?, NOW())");
            $stmtInsertNB->execute([$nb_nome, $nb_ip, $nb_status]);
            $nb_id = $db->lastInsertId();
            
            // Gravar log de auditoria
            require_once __DIR__ . '/../app/models/AuditLog.php';
            AuditLog::write($db, null, 'Dispositivo auto-registrado', "Nobreak: $nb_nome, IP: $nb_ip, Associado a: $hostname");
        }
    } else {
        $nobreak = null;
    }

    if ($nobreak && isset($nb_id)) {

        // Salvar leituras para o Nobreak
        $nb_sensores = [];
        if ($nb_bateria !== null) {
            $nb_sensores[] = ['nome' => 'Capacidade da Bateria (%)', 'tipo' => 'bateria', 'valor' => $nb_bateria];
        }
        if ($nb_autonomia !== null) {
            $nb_sensores[] = ['nome' => 'Tempo de Autonomia (Minutos)', 'tipo' => 'uptime', 'valor' => $nb_autonomia];
        }
        if ($nb_tensao !== null && $nb_tensao > 0 && $nb_tensao < 500) {
            $nb_sensores[] = ['nome' => 'Tensão de Entrada (V)', 'tipo' => 'tensao', 'valor' => $nb_tensao];
        }
        if ($nb_carga !== null) {
            $nb_sensores[] = ['nome' => 'Carga (%)', 'tipo' => 'carga_nobreak', 'valor' => $nb_carga];
        }

        foreach ($nb_sensores as $s) {
            $sensor_id = $getOrCreateSensor($db, $nb_id, $s['nome'], $s['tipo']);
            $stmtInsert->execute([$sensor_id, $s['valor']]);
        }
    }

    echo json_encode([
        'status' => 'sucesso',
        'mensagem' => 'Dados e conexões recebidos e salvos no banco.',
        'recebido_em' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao salvar no banco: ' . $e->getMessage()]);
}
