<?php
require 'config/database.php';
$db = (new Database())->getConnection();
try {
    $db->exec("ALTER TABLE dispositivos MODIFY status ENUM('online', 'inativo', 'offline') DEFAULT 'offline'");
    echo "Coluna status modificada com sucesso.\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
