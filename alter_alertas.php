<?php
require 'config/database.php';
$db = (new Database())->getConnection();
try {
    $db->exec("ALTER TABLE alertas ADD COLUMN notificado_em TIMESTAMP NULL DEFAULT NULL AFTER criado_em");
    echo "Coluna notificado_em adicionada.\n";
} catch (Exception $e) {
    echo "Erro notificado_em: " . $e->getMessage() . "\n";
}
try {
    $db->exec("ALTER TABLE alertas ADD COLUMN resolvido_notificado_em TIMESTAMP NULL DEFAULT NULL AFTER resolvido_em");
    echo "Coluna resolvido_notificado_em adicionada.\n";
} catch (Exception $e) {
    echo "Erro resolvido_notificado_em: " . $e->getMessage() . "\n";
}
