<?php
// Arquivo: api/receber_agente.php
// Objetivo: Receber dados de agentes locais instalados nos servidores (Zabbix Active Agent style)

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

// Na vida real, validaríamos um TOKEN (Bearer) para garantir a segurança
if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    // if (!isset($headers['Authorization']) || $headers['Authorization'] !== 'Bearer SEU_TOKEN_AQUI') {
    //     http_response_code(401);
    //     echo json_encode(['erro' => 'Não autorizado']);
    //     exit;
    // }
}

// Ler o JSON enviado pelo agente (via POST)
$dados_json = file_get_contents("php://input");
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

    // 3. Preparar sensores para salvar
    $sensoresParaSalvar = [
        ['nome' => 'CPU Load', 'tipo' => 'cpu', 'valor' => $cpu],
        ['nome' => 'RAM Livre (MB)', 'tipo' => 'ram', 'valor' => $mem_livre],
        ['nome' => 'RAM Total (MB)', 'tipo' => 'ram', 'valor' => $mem_total]
    ];

    // Adicionar discos
    foreach ($discos as $disco) {
        $letra = $disco['letra'] ?? 'C:';
        $livre = $disco['livre_gb'] ?? 0;
        $sensoresParaSalvar[] = ['nome' => "Disco Livre ($letra) GB", 'tipo' => 'disco', 'valor' => $livre];
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
            // Nao cria nobreak automaticamente (evita bateria de notebook/servidor sem UPS)
            $nobreak = null;
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
