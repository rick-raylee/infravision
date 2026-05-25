<?php
require 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare("UPDATE dispositivos SET tipo = 'computador' WHERE nome = 'WIN-DMLNHL9INL9'");
$stmt->execute();
echo 'Success!';
