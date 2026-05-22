<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["erro" => "Sem conexao com o banco"]);
    exit;
}

// 1. Temperatura e umidade (sensor ambiental)
$stmtTemp = $db->prepare("SELECT l.valor FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.tipo = 'temperatura' ORDER BY l.data_leitura DESC LIMIT 1");
$stmtTemp->execute();
$tempRow = $stmtTemp->fetch(PDO::FETCH_ASSOC);
$temp = $tempRow ? round($tempRow['valor']) : null;

$stmtUmid = $db->prepare("SELECT l.valor FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.tipo = 'umidade' ORDER BY l.data_leitura DESC LIMIT 1");
$stmtUmid->execute();
$umidRow = $stmtUmid->fetch(PDO::FETCH_ASSOC);
$umid = $umidRow ? round($umidRow['valor']) : null;

// 2. Tráfego de rede real - sensores rede_in / rede_out (agente C# v2+)
$stmtIn = $db->prepare(
    "SELECT l.valor, l.data_leitura, s.nome as sensor_nome FROM leituras l
     JOIN sensores s ON l.sensor_id = s.id
     WHERE s.tipo = 'rede_in'
     ORDER BY l.data_leitura DESC LIMIT 11"
);
$stmtIn->execute();
$rowsIn = array_reverse($stmtIn->fetchAll(PDO::FETCH_ASSOC));

$stmtOut = $db->prepare(
    "SELECT l.valor, l.data_leitura FROM leituras l
     JOIN sensores s ON l.sensor_id = s.id
     WHERE s.tipo = 'rede_out'
     ORDER BY l.data_leitura DESC LIMIT 11"
);
$stmtOut->execute();
$rowsOut = array_reverse($stmtOut->fetchAll(PDO::FETCH_ASSOC));

$netIn  = array_map(function($r) { return round((float)$r['valor'], 3); }, $rowsIn);
$netOut = array_map(function($r) { return round((float)$r['valor'], 3); }, $rowsOut);
$labels = array_map(function($r) { return date('H:i', strtotime($r['data_leitura'])); }, $rowsIn);

$interface_name = 'Rede In (Mbps)';

// Tentar buscar o nome do provedor (ISP)
$cache_file = sys_get_temp_dir() . '/infravision_isp.txt';
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 86400) {
    $interface_name = file_get_contents($cache_file);
} else {
    $ch = curl_init('http://ip-api.com/json/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $res = curl_exec($ch);
    curl_close($ch);
    if ($res) {
        $json = json_decode($res, true);
        if (isset($json['isp']) && !empty($json['isp'])) {
            $interface_name = $json['isp'];
            file_put_contents($cache_file, $interface_name);
        }
    }
}

// Fallback: agentes antigos sem rede_in/rede_out -> usa contagem de conexoes por momento
if (empty($netIn)) {
    $stmtFallback = $db->query(
        "SELECT DATE_FORMAT(atualizado_em, '%H:%i') as t, COUNT(*) as n,
                MAX(atualizado_em) as ts
         FROM conexoes
         GROUP BY DATE_FORMAT(atualizado_em, '%H:%i')
         ORDER BY MAX(atualizado_em) DESC LIMIT 11"
    );
    $fbRows  = array_reverse($stmtFallback->fetchAll(PDO::FETCH_ASSOC));
    $labels  = array_column($fbRows, 't');
    $netIn   = array_map(function($r) { return (float)$r['n']; }, $fbRows);
    $netOut  = array_fill(0, count($fbRows), 0);
    $interface_name = 'Conexões Ativas';
}

// Completar com zeros e labels vazios se menos de 11 pontos
while (count($netIn)  < 11) { array_unshift($netIn,  0); array_unshift($labels, '--:--'); }
while (count($netOut) < 11) { array_unshift($netOut, 0); }

$data = [
    'temperatura' => $temp,
    'umidade'     => $umid,
    'rede' => [
        'labels' => $labels,
        'in'     => $netIn,
        'out'    => $netOut,
        'interface' => $interface_name
    ]
];

echo json_encode($data);
