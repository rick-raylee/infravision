<?php
require 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$stmt = $db->query("SELECT * FROM dispositivos WHERE id = 4");
var_dump($stmt->fetch(PDO::FETCH_ASSOC));
