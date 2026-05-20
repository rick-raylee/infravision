<?php

class AlertController {
    
    public function index() {
        require_once 'app/models/Alert.php';
        $database = new Database();
        $db = $database->getConnection();
        $alertModel = new Alert($db);
        $alertas = $alertModel->readAll()->fetchAll(PDO::FETCH_ASSOC);

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        require 'app/views/layout/header.php';
        require 'app/views/alert/index.php';
        require 'app/views/layout/footer.php';
    }

    public function acknowledge() {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? null;
        
        if ($id) {
            require_once 'app/models/Alert.php';
            $database = new Database();
            $db = $database->getConnection();
            $alertModel = new Alert($db);
            
            if ($alertModel->acknowledge($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar banco']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID não fornecido']);
        }
        exit;
    }

    public function generateTest() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');

        require_once 'app/models/Alert.php';
        $database = new Database();
        $db = $database->getConnection();
        
        // Buscar um dispositivo qualquer para associar o alerta
        $query = "SELECT id FROM dispositivos LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$device) {
            header("Location: " . $base_path . "/alerts?erro=sem_dispositivos");
            exit;
        }
        $device_id = $device['id'];

        $msg = "ALERTA DE TESTE: Saturação de CPU detectada às " . date('H:i:s');
        $query = "INSERT INTO alertas (dispositivo_id, mensagem, severidade, status) VALUES (:dev, :msg, 'critico', 'ativo')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':dev', $device_id);
        $stmt->bindParam(':msg', $msg);
        $stmt->execute();

        header("Location: " . $base_path . "/alerts");
        exit;
    }
}
