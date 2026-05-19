<?php

class AlertContactController {
    
    public function index() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: /infravision/dashboard");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, nome, tipo, destino, ativo FROM contatos_alerta ORDER BY nome ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $base_path = '/infravision';
        require 'app/views/layout/header.php';
        require 'app/views/alert_contact/index.php';
        require 'app/views/layout/footer.php';
    }

    public function create() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: /infravision/dashboard");
            exit;
        }

        $base_path = '/infravision';
        require 'app/views/layout/header.php';
        require 'app/views/alert_contact/create.php';
        require 'app/views/layout/footer.php';
    }

    public function store() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $nome = $_POST['nome'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $destino = $_POST['destino'] ?? '';

        $query = "INSERT INTO contatos_alerta (nome, tipo, destino) VALUES (:nome, :tipo, :destino)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':destino', $destino);

        if ($stmt->execute()) {
            header("Location: /infravision/alert-contacts");
        } else {
            echo "Erro ao cadastrar contato.";
        }
    }
}
