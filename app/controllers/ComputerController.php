<?php

class ComputerController {
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $computadores = [];
        if ($db) {
            $query = "SELECT d.*, 
                             (SELECT l.valor FROM leituras l 
                              JOIN sensores s ON l.sensor_id = s.id 
                              WHERE s.dispositivo_id = d.id AND s.tipo = 'cpu' 
                              ORDER BY l.data_leitura DESC LIMIT 1) as cpu,
                             (SELECT l.valor FROM leituras l 
                              JOIN sensores s ON l.sensor_id = s.id 
                              WHERE s.dispositivo_id = d.id AND s.tipo = 'ram' AND s.nome = 'RAM Livre (MB)' 
                              ORDER BY l.data_leitura DESC LIMIT 1) as ram

                      FROM dispositivos d
                      WHERE d.tipo = 'computador'
                      ORDER BY d.criado_em DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $computadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/computer/index.php';
        require 'app/views/layout/footer.php';
    }

    public function create() {
        // Apenas admin
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: " . BASE_PATH . "/computers");
            exit;
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        require 'app/views/layout/header.php';
        require 'app/views/computer/create.php';
        require 'app/views/layout/footer.php';
    }

    public function store() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        $nome = trim($_POST['nome'] ?? '');
        $ip = trim($_POST['ip'] ?? '');
        $sistema_operacional = trim($_POST['sistema_operacional'] ?? null);
        $processador = trim($_POST['processador'] ?? null);
        $fabricante = trim($_POST['fabricante'] ?? null);
        $modelo = trim($_POST['modelo'] ?? null);
        $numero_serie = trim($_POST['numero_serie'] ?? null);
        $funcionario = trim($_POST['funcionario'] ?? null);
        $setor = trim($_POST['setor'] ?? null);
        $patrimonio = trim($_POST['patrimonio'] ?? null);
        $data_entrega = !empty($_POST['data_entrega']) ? $_POST['data_entrega'] : null;

        if (!empty($nome) && !empty($ip) && $db) {
            $query = "INSERT INTO dispositivos (nome, ip, tipo, status, sistema_operacional, processador, fabricante, modelo, numero_serie, funcionario, setor, patrimonio, data_entrega) 
                      VALUES (:nome, :ip, 'computador', 'offline', :sistema_operacional, :processador, :fabricante, :modelo, :numero_serie, :funcionario, :setor, :patrimonio, :data_entrega)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':nome' => $nome,
                ':ip' => $ip,
                ':sistema_operacional' => $sistema_operacional,
                ':processador' => $processador,
                ':fabricante' => $fabricante,
                ':modelo' => $modelo,
                ':numero_serie' => $numero_serie,
                ':funcionario' => $funcionario,
                ':setor' => $setor,
                ':patrimonio' => $patrimonio,
                ':data_entrega' => $data_entrega
            ]);

            // Log de auditoria
            if (file_exists('app/models/AuditLog.php')) {
                require_once 'app/models/AuditLog.php';
                AuditLog::write($db, $_SESSION['usuario_id'] ?? null, 'Computador cadastrado', "Nome: $nome, IP: $ip, Colaborador: $funcionario");
            }
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        header("Location: $base_path/computers");
        exit;
    }

    public function edit() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: " . BASE_PATH . "/computers");
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        $c = null;
        if ($id > 0 && $db) {
            $stmt = $db->prepare("SELECT * FROM dispositivos WHERE id = :id AND tipo = 'computador'");
            $stmt->execute([':id' => $id]);
            $c = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$c) {
            $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
            header("Location: $base_path/computers");
            exit;
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        require 'app/views/layout/header.php';
        require 'app/views/computer/edit.php';
        require 'app/views/layout/footer.php';
    }

    public function update() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        $id = intval($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $ip = trim($_POST['ip'] ?? '');
        $sistema_operacional = trim($_POST['sistema_operacional'] ?? null);
        $processador = trim($_POST['processador'] ?? null);
        $fabricante = trim($_POST['fabricante'] ?? null);
        $modelo = trim($_POST['modelo'] ?? null);
        $numero_serie = trim($_POST['numero_serie'] ?? null);
        $funcionario = trim($_POST['funcionario'] ?? null);
        $setor = trim($_POST['setor'] ?? null);
        $patrimonio = trim($_POST['patrimonio'] ?? null);
        $data_entrega = !empty($_POST['data_entrega']) ? $_POST['data_entrega'] : null;

        if ($id > 0 && !empty($nome) && !empty($ip) && $db) {
            $query = "UPDATE dispositivos SET 
                        nome = :nome, 
                        ip = :ip, 
                        sistema_operacional = :sistema_operacional, 
                        processador = :processador, 
                        fabricante = :fabricante, 
                        modelo = :modelo, 
                        numero_serie = :numero_serie, 
                        funcionario = :funcionario, 
                        setor = :setor, 
                        patrimonio = :patrimonio, 
                        data_entrega = :data_entrega 
                      WHERE id = :id AND tipo = 'computador'";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':nome' => $nome,
                ':ip' => $ip,
                ':sistema_operacional' => $sistema_operacional,
                ':processador' => $processador,
                ':fabricante' => $fabricante,
                ':modelo' => $modelo,
                ':numero_serie' => $numero_serie,
                ':funcionario' => $funcionario,
                ':setor' => $setor,
                ':patrimonio' => $patrimonio,
                ':data_entrega' => $data_entrega,
                ':id' => $id
            ]);

            // Log de auditoria
            if (file_exists('app/models/AuditLog.php')) {
                require_once 'app/models/AuditLog.php';
                AuditLog::write($db, $_SESSION['usuario_id'] ?? null, 'Computador atualizado', "Nome: $nome, IP: $ip");
            }
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        header("Location: $base_path/computers");
        exit;
    }

    public function delete() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        $id = intval($_GET['id'] ?? 0);
        
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        if ($id > 0 && $db) {
            // Obter detalhes antes de excluir
            $stmtDetails = $db->prepare("SELECT nome FROM dispositivos WHERE id = :id AND tipo = 'computador'");
            $stmtDetails->execute([':id' => $id]);
            $nome = $stmtDetails->fetchColumn();

            if ($nome) {
                $query = "DELETE FROM dispositivos WHERE id = :id AND tipo = 'computador'";
                $stmt = $db->prepare($query);
                $stmt->execute([':id' => $id]);

                // Log de auditoria
                if (file_exists('app/models/AuditLog.php')) {
                    require_once 'app/models/AuditLog.php';
                    AuditLog::write($db, $_SESSION['usuario_id'] ?? null, 'Computador removido', "Nome: $nome");
                }
            }
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        header("Location: $base_path/computers");
        exit;
    }

    public function updatePeripherals() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();

            $id = $_POST['id'] ?? null;
            $tipo_periferico = $_POST['tipo_periferico'] ?? ''; // 'mouse' ou 'teclado'
            $data = $_POST['data'] ?? date('Y-m-d');

            if ($id && $db) {
                $params = [$data, $id];

                if ($tipo_periferico === 'mouse') {
                    $query = "UPDATE dispositivos SET mouse_trocado_em = ? WHERE id = ?";
                } elseif ($tipo_periferico === 'teclado') {
                    $query = "UPDATE dispositivos SET teclado_trocado_em = ? WHERE id = ?";
                } elseif ($tipo_periferico === 'entrega_save') {
                    $query = "UPDATE dispositivos SET data_entrega = ? WHERE id = ?";
                } elseif ($tipo_periferico === 'entrega_delete') {
                    $query = "UPDATE dispositivos SET data_entrega = NULL WHERE id = ?";
                    $params = [$id];
                } elseif ($tipo_periferico === 'funcionario_save') {
                    $query = "UPDATE dispositivos SET funcionario = ?, setor = ? WHERE id = ?";
                    $params = [$_POST['funcionario'] ?? '', $_POST['setor'] ?? '', $id];
                } elseif ($tipo_periferico === 'funcionario_delete') {
                    $query = "UPDATE dispositivos SET funcionario = NULL, setor = NULL WHERE id = ?";
                    $params = [$id];
                } elseif ($tipo_periferico === 'patrimonio_save') {
                    $query = "UPDATE dispositivos SET patrimonio = ? WHERE id = ?";
                    $params = [$_POST['patrimonio'] ?? '', $id];
                } elseif ($tipo_periferico === 'patrimonio_delete') {
                    $query = "UPDATE dispositivos SET patrimonio = NULL WHERE id = ?";
                    $params = [$id];
                }

                if (isset($query)) {
                    $stmt = $db->prepare($query);
                    $stmt->execute($params);
                }
            }
        }

        header("Location: $base_path/computers");
        exit;
    }
}
