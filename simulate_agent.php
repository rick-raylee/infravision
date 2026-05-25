<?php
// Script para simular o agente enviando dados para o banco
require 'config/database.php';
$db = (new Database())->getConnection();

// Buscar um dispositivo
$stmt = $db->query("SELECT id FROM dispositivos LIMIT 1");
$dispositivo = $stmt->fetch();
if (!$dispositivo) {
    echo "Nenhum dispositivo encontrado.";
    exit;
}
$dispositivo_id = $dispositivo['id'];

// Garantir que sensores de rede existem
$stmt = $db->prepare("SELECT id FROM sensores WHERE dispositivo_id = ? AND tipo = ?");
$stmt->execute([$dispositivo_id, 'rede_in']);
$sensor_in = $stmt->fetchColumn();
if (!$sensor_in) {
    $db->prepare("INSERT INTO sensores (dispositivo_id, nome, tipo, ativo) VALUES (?, 'Rede In (Mbps)', 'rede_in', 1)")->execute([$dispositivo_id]);
    $sensor_in = $db->lastInsertId();
}

$stmt->execute([$dispositivo_id, 'rede_out']);
$sensor_out = $stmt->fetchColumn();
if (!$sensor_out) {
    $db->prepare("INSERT INTO sensores (dispositivo_id, nome, tipo, ativo) VALUES (?, 'Rede Out (Mbps)', 'rede_out', 1)")->execute([$dispositivo_id]);
    $sensor_out = $db->lastInsertId();
}

$in = rand(10, 500) / 100;
$out = rand(10, 1500) / 100;

$db->prepare("INSERT INTO leituras (sensor_id, valor, data_leitura) VALUES (?, ?, NOW())")->execute([$sensor_in, $in]);
$db->prepare("INSERT INTO leituras (sensor_id, valor, data_leitura) VALUES (?, ?, NOW())")->execute([$sensor_out, $out]);

echo "Dados simulados inseridos: In $in Mbps, Out $out Mbps.\n";
