<?php
header('Content-Type: application/json');

// Adiciona compatibilidade para rodar no MVC
require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["erro" => "Não foi possível conectar ao banco de dados"]);
    exit;
}

// Buscar URLs do banco
$stmt = $db->query("SELECT * FROM urls_monitoradas ORDER BY id ASC");
$urls_to_check = $stmt->fetchAll(PDO::FETCH_ASSOC);

$resultados_urls = [];
foreach ($urls_to_check as $site) {
    $ch = curl_init($site['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    
    $start_time = microtime(true);
    curl_exec($ch);
    $end_time = microtime(true);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $latency = $http_code ? round(($end_time - $start_time) * 1000) : 0;
    $curl_err = curl_error($ch);
    curl_close($ch);
    
    $status_class = ($http_code >= 200 && $http_code < 400) ? 'bg-success' : 'bg-danger';
    $status_text = $http_code ? $http_code : 'Offline';
    if ($http_code == 200) $status_text = '200 OK';
    
    $resultados_urls[] = [
        'id' => $site['id'],
        'nome' => $site['nome'],
        'url' => $site['url'],
        'status_class' => $status_class,
        'status_text' => $status_text,
        'latency' => $latency . 'ms',
        'uptime' => '100%',
        'curl_error' => $curl_err
    ];
}

echo json_encode($resultados_urls);
