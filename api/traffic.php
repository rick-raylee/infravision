<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$conexoes = [];
$stats = [
    'total' => 0,
    'top_service' => null,
    'top_service_detail' => null,
    'top_consumer' => null,
    'top_consumer_ip' => null,
];

if ($db) {
    $query = "SELECT c.origem AS origin, c.ip_origem AS ip, c.destino, c.servico AS service, CONCAT(c.latencia, 'ms') AS latency, c.carga AS `load`
              FROM conexoes c
              JOIN dispositivos d ON c.dispositivo_id = d.id
              WHERE d.status = 'online'
              ORDER BY c.id DESC LIMIT 50";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $conexoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtTotal = $db->query("SELECT COUNT(*) FROM conexoes c JOIN dispositivos d ON c.dispositivo_id = d.id WHERE d.status = 'online'");
    $stats['total'] = $stmtTotal ? (int)$stmtTotal->fetchColumn() : 0;

    if ($stats['total'] > 0) {
        $serviceCounts = [];
        $topLoad = -1;
        foreach ($conexoes as $row) {
            $service = trim((string)($row['service'] ?? ''));
            if ($service !== '') {
                $serviceCounts[$service] = ($serviceCounts[$service] ?? 0) + 1;
            }
            $load = (float)($row['load'] ?? 0);
            if ($load >= $topLoad) {
                $topLoad = $load;
                $stats['top_consumer'] = $row['origin'] ?? $row['origem'] ?? null;
                $stats['top_consumer_ip'] = $row['ip'] ?? null;
            }
        }
        if (!empty($serviceCounts)) {
            arsort($serviceCounts);
            $stats['top_service'] = array_key_first($serviceCounts);
            $stats['top_service_detail'] = $stats['top_service'];
        }
    }
}

echo json_encode([
    'conexoes' => $conexoes,
    'stats' => $stats
]);
