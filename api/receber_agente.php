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
        // Atualizar status e ultimo_check
        $stmt = $db->prepare("UPDATE dispositivos SET status = 'online', ultimo_check = NOW() WHERE id = ?");
        $stmt->execute([$dispositivo_id]);
    } else {
        // 2. Auto-discovery: Cadastrar dispositivo
        $stmt = $db->prepare("INSERT INTO dispositivos (nome, ip, tipo, status, ultimo_check) VALUES (?, ?, 'servidor_windows', 'online', NOW())");
        $stmt->execute([$hostname, $ip]);
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

    echo json_encode([
        'status' => 'sucesso',
        'mensagem' => 'Dados e conexões recebidos e salvos no banco.',
        'recebido_em' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao salvar no banco: ' . $e->getMessage()]);
}
