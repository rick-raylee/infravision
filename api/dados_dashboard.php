<?php
header('Content-Type: application/json');

// Simulação de dados vindos de sensores SNMP e agentes
$data = [
    'temperatura' => rand(20, 24),
    'umidade' => rand(40, 55),
    'rede' => [
        'in' => [rand(30, 100), rand(30, 100), rand(30, 100), rand(30, 100), rand(30, 100), rand(30, 100), rand(30, 100), rand(30, 100), rand(30, 100), rand(30, 100), rand(30, 100)],
        'out' => [rand(20, 80), rand(20, 80), rand(20, 80), rand(20, 80), rand(20, 80), rand(20, 80), rand(20, 80), rand(20, 80), rand(20, 80), rand(20, 80), rand(20, 80)]
    ]
];

echo json_encode($data);
