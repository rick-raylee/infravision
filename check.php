<?php
require 'config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT s.nome, l.valor, l.data_leitura FROM leituras l JOIN sensores s ON l.sensor_id = s.id WHERE s.tipo IN ('rede_in', 'rede_out') ORDER BY l.data_leitura DESC LIMIT 5");
print_r($stmt->fetchAll());
