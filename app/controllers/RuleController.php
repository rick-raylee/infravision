<?php

class RuleController {
    
    public function index() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: " . BASE_PATH . "/dashboard");
            exit;
        }

        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        $regras_performance = [];
        $regras_disponibilidade = [];

        if ($db) {
            // Auto-criar a tabela se não existir
            $db->exec("CREATE TABLE IF NOT EXISTS regras_alerta (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                categoria ENUM('performance', 'disponibilidade') NOT NULL,
                condicao VARCHAR(100) NOT NULL,
                limite_aviso VARCHAR(50) DEFAULT NULL,
                limite_critico VARCHAR(50) DEFAULT NULL,
                tempo_resposta VARCHAR(50) DEFAULT NULL,
                tentativas INT DEFAULT 1,
                acao VARCHAR(100) NOT NULL,
                ativo BOOLEAN DEFAULT TRUE,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Realizar seeding se estiver vazia
            $count = $db->query("SELECT COUNT(*) FROM regras_alerta")->fetchColumn();
            if ($count == 0) {
                $db->exec("INSERT INTO regras_alerta (nome, categoria, condicao, limite_aviso, limite_critico, tempo_resposta, tentativas, acao, ativo) VALUES
                    ('CPU Load', 'performance', 'Maior que (>)', '75%', '90%', NULL, 1, 'Painel + Email', 1),
                    ('Memória RAM', 'performance', 'Maior que (>)', '80%', '95%', NULL, 1, 'Painel + Telegram', 1),
                    ('Latência de Disco', 'performance', 'Maior que (>)', '10ms', '25ms', NULL, 1, 'Painel', 1),
                    ('Ping (ICMP)', 'disponibilidade', 'Sem Resposta', NULL, NULL, '-', 3, 'Alerta Crítico', 1),
                    ('Serviço Web (HTTP)', 'disponibilidade', 'Código != 200', NULL, NULL, '> 5000ms', 2, 'Aviso', 0)
                ");
            }

            // Buscar regras
            $stmt = $db->prepare("SELECT * FROM regras_alerta WHERE categoria = 'performance' ORDER BY id ASC");
            $stmt->execute();
            $regras_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("SELECT * FROM regras_alerta WHERE categoria = 'disponibilidade' ORDER BY id ASC");
            $stmt->execute();
            $regras_disponibilidade = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        require 'app/views/layout/header.php';
        require 'app/views/rule/index.php';
        require 'app/views/layout/footer.php';
    }

    public function store() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');

        if ($db) {
            $id = intval($_POST['id'] ?? 0);
            $nome = $_POST['nome'] ?? '';
            $categoria = $_POST['categoria'] ?? 'performance';
            $condicao = $_POST['condicao'] ?? '';
            $limite_aviso = isset($_POST['limite_aviso']) && $_POST['limite_aviso'] !== '' ? $_POST['limite_aviso'] : null;
            $limite_critico = isset($_POST['limite_critico']) && $_POST['limite_critico'] !== '' ? $_POST['limite_critico'] : null;
            $tempo_resposta = isset($_POST['tempo_resposta']) && $_POST['tempo_resposta'] !== '' ? $_POST['tempo_resposta'] : null;
            $tentativas = intval($_POST['tentativas'] ?? 1);
            $acao = $_POST['acao'] ?? '';
            $ativo = isset($_POST['ativo']) ? 1 : 0;

            if ($id > 0) {
                // Update
                $query = "UPDATE regras_alerta SET 
                            nome = :nome, 
                            categoria = :categoria, 
                            condicao = :condicao, 
                            limite_aviso = :limite_aviso, 
                            limite_critico = :limite_critico, 
                            tempo_resposta = :tempo_resposta, 
                            tentativas = :tentativas, 
                            acao = :acao, 
                            ativo = :ativo 
                          WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':nome' => $nome,
                    ':categoria' => $categoria,
                    ':condicao' => $condicao,
                    ':limite_aviso' => $limite_aviso,
                    ':limite_critico' => $limite_critico,
                    ':tempo_resposta' => $tempo_resposta,
                    ':tentativas' => $tentativas,
                    ':acao' => $acao,
                    ':ativo' => $ativo,
                    ':id' => $id
                ]);
            } else {
                // Create
                $query = "INSERT INTO regras_alerta 
                            (nome, categoria, condicao, limite_aviso, limite_critico, tempo_resposta, tentativas, acao, ativo) 
                          VALUES 
                            (:nome, :categoria, :condicao, :limite_aviso, :limite_critico, :tempo_resposta, :tentativas, :acao, :ativo)";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':nome' => $nome,
                    ':categoria' => $categoria,
                    ':condicao' => $condicao,
                    ':limite_aviso' => $limite_aviso,
                    ':limite_critico' => $limite_critico,
                    ':tempo_resposta' => $tempo_resposta,
                    ':tentativas' => $tentativas,
                    ':acao' => $acao,
                    ':ativo' => $ativo
                ]);
            }
        }

        header("Location: " . $base_path . "/rules");
        exit;
    }

    public function delete() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');

        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();

            if ($db) {
                $stmt = $db->prepare("DELETE FROM regras_alerta WHERE id = :id");
                $stmt->execute([':id' => $id]);
            }
        }

        header("Location: " . $base_path . "/rules");
        exit;
    }
}
