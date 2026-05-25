<?php
require 'config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT id, status, notificado_em FROM alertas");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
