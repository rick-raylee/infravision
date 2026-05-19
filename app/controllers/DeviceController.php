<?php

class DeviceController {
    
    public function create() {
        // Verificar se é admin
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: /infravision/dashboard");
            exit;
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : '/infravision';
        require 'app/views/layout/header.php';
        require 'app/views/device/create.php';
        require 'app/views/layout/footer.php';
    }

    public function store() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        require_once 'app/models/Device.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $nome = $_POST['nome'] ?? '';
        $ip = $_POST['ip'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $status = 'online'; // Padrão ao cadastrar

        $query = "INSERT INTO dispositivos (nome, ip, tipo, status) VALUES (:nome, :ip, :tipo, :status)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            header("Location: /infravision/servers");
        } else {
            echo "Erro ao cadastrar dispositivo.";
        }
    }
}
