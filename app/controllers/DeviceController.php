<?php

class DeviceController {
    
    public function create() {
        // Verificar se é admin
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: " . BASE_PATH . "/dashboard");
            exit;
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
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
            // Gravar log de auditoria
            require_once 'app/models/AuditLog.php';
            AuditLog::write($db, $_SESSION['usuario_id'] ?? null, 'Dispositivo cadastrado', "Nome: $nome, IP: $ip, Tipo: $tipo");

            header("Location: " . BASE_PATH . "/servers");
        } else {
            echo "Erro ao cadastrar dispositivo.";
        }
    }

    public function edit() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: " . BASE_PATH . "/dashboard");
            exit;
        }
        $id = intval($_GET['id'] ?? 0);
        
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM dispositivos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $srv = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$srv) {
            header("Location: " . BASE_PATH . "/servers");
            exit;
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        require 'app/views/layout/header.php';
        require 'app/views/device/edit.php';
        require 'app/views/layout/footer.php';
    }

    public function update() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        $id = intval($_POST['id'] ?? 0);
        $nome = $_POST['nome'] ?? '';
        $ip = $_POST['ip'] ?? '';
        $tipo = $_POST['tipo'] ?? '';

        if ($id > 0 && !empty($nome) && !empty($ip)) {
            $database = new Database();
            $db = $database->getConnection();

            $query = "UPDATE dispositivos SET nome = :nome, ip = :ip, tipo = :tipo WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':nome' => $nome,
                ':ip' => $ip,
                ':tipo' => $tipo,
                ':id' => $id
            ]);

            require_once 'app/models/AuditLog.php';
            AuditLog::write($db, $_SESSION['usuario_id'] ?? null, 'Dispositivo atualizado', "Nome: $nome, IP: $ip, Tipo: $tipo");
        }

        header("Location: " . BASE_PATH . "/servers");
        exit;
    }

    public function delete() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            $database = new Database();
            $db = $database->getConnection();
            
            // Obter detalhes para auditoria
            $stmtDetails = $db->prepare("SELECT nome FROM dispositivos WHERE id = :id");
            $stmtDetails->execute([':id' => $id]);
            $nome = $stmtDetails->fetchColumn();

            if ($nome) {
                $query = "DELETE FROM dispositivos WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([':id' => $id]);

                require_once 'app/models/AuditLog.php';
                AuditLog::write($db, $_SESSION['usuario_id'] ?? null, 'Dispositivo removido', "Nome: $nome");
            }
        }
        header("Location: " . BASE_PATH . "/servers");
        exit;
    }
}
