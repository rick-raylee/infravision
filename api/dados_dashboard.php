<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["erro" => "Sem conexao com o banco"]);
    exit;
}

// 1. Buscar a temperatura e umidade média atual (últimas leituras) dos sensores
$queryTemp = "SELECT l.valor FROM leituras l 
              JOIN sensores s ON l.sensor_id = s.id 
              WHERE s.tipo = 'temperatura' 
              ORDER BY l.data_leitura DESC LIMIT 1";
$stmtTemp = $db->prepare($queryTemp);
$stmtTemp->execute();
$tempRow = $stmtTemp->fetch(PDO::FETCH_ASSOC);
$temp = $tempRow ? round($tempRow['valor']) : 22; // fallback to 22 if no real sensor

$queryUmid = "SELECT l.valor FROM leituras l 
              JOIN sensores s ON l.sensor_id = s.id 
              WHERE s.tipo = 'umidade' 
              ORDER BY l.data_leitura DESC LIMIT 1";
$stmtUmid = $db->prepare($queryUmid);
$stmtUmid->execute();
$umidRow = $stmtUmid->fetch(PDO::FETCH_ASSOC);
$umid = $umidRow ? round($umidRow['valor']) : 45; // fallback to 45 if no real sensor

// 2. Buscar tráfego de rede (rede_in e rede_out) dos últimos 11 checks
$queryNetIn = "SELECT l.valor FROM leituras l 
               JOIN sensores s ON l.sensor_id = s.id 
               WHERE s.tipo = 'rede_in' 
               ORDER BY l.data_leitura DESC LIMIT 11";
$stmtNetIn = $db->prepare($queryNetIn);
$stmtNetIn->execute();
$netInReadings = array_reverse($stmtNetIn->fetchAll(PDO::FETCH_COLUMN));

$queryNetOut = "SELECT l.valor FROM leituras l 
                JOIN sensores s ON l.sensor_id = s.id 
                WHERE s.tipo = 'rede_out' 
                ORDER BY l.data_leitura DESC LIMIT 11";
$stmtNetOut = $db->prepare($queryNetOut);
$stmtNetOut->execute();
$netOutReadings = array_reverse($stmtNetOut->fetchAll(PDO::FETCH_COLUMN));

// Preencher com zeros se tiver menos que 11 leituras
while (count($netInReadings) < 11) {
    array_unshift($netInReadings, 0);
}
while (count($netOutReadings) < 11) {
    array_unshift($netOutReadings, 0);
}

$data = [
    'temperatura' => $temp,
    'umidade' => $umid,
    'rede' => [
        'in' => array_map('floatval', $netInReadings),
        'out' => array_map('floatval', $netOutReadings)
    ]
];

echo json_encode($data);
