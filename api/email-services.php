<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["erro" => "Não foi possível conectar ao banco de dados"]);
    exit;
}

// Buscar servidores de email do banco
$stmt = $db->query("SELECT * FROM servidores_email ORDER BY id ASC");
$servidores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$resultados = [];
foreach ($servidores as $srv) {
    // Realizar verificação de porta (socket connection) em tempo real
    $start_time = microtime(true);
    // Tenta abrir conexão socket. Usa timeout curto de 1.5s
    $fp = @fsockopen($srv['host'], $srv['porta'], $errno, $errstr, 1.5);
    $end_time = microtime(true);
    
    $latency = round(($end_time - $start_time) * 1000);
    
    if ($fp) {
        $status_class = 'bg-success';
        $status_text = 'Online';
        $is_online = true;
        fclose($fp);
    } else {
        $status_class = 'bg-danger';
        $status_text = 'Offline';
        $is_online = false;
        // Evita mostrar latências gigantescas em falhas completas
        $latency = 0;
    }
    
    $resultados[] = [
        'id' => $srv['id'],
        'nome' => $srv['nome'],
        'host' => $srv['host'],
        'tipo' => $srv['tipo'],
        'porta' => $srv['porta'],
        'status_class' => $status_class,
        'status_text' => $status_text,
        'latency' => $latency . 'ms',
        'is_online' => $is_online,
        'fila_mensagens' => $srv['fila_mensagens'],
        'mailbox_db' => $is_online ? $srv['mailbox_db'] : 'Dismounted',
        'transport_svc' => $is_online ? $srv['transport_svc'] : 'Stopped',
        'active_sync' => $is_online ? $srv['active_sync'] : 'Failed',
        'outlook_anywhere' => $is_online ? $srv['outlook_anywhere'] : 'Failed',
        'dag_replication' => $is_online ? $srv['dag_replication'] : 'Failed',
        'socket_error' => $errstr
    ];
}

echo json_encode($resultados);
