<?php

class ServiceMonitorController {
    
    private function checkAndInitTable($db) {
        // Criar tabela de URLs se não existir
        $db->exec("CREATE TABLE IF NOT EXISTS urls_monitoradas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            url VARCHAR(255) NOT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Inserir registro padrão se estiver vazia
        $stmtCheck = $db->query("SELECT COUNT(*) FROM urls_monitoradas");
        $count = $stmtCheck->fetchColumn();
        if ($count == 0) {
            $db->exec("INSERT INTO urls_monitoradas (nome, url) VALUES ('Rodomax Atua', 'https://rodomax.atua.com.br/')");
        }

        // Corrigir possíveis erros de digitação de protocolo nas URLs registradas (ex: htps:// -> https://)
        $db->exec("UPDATE urls_monitoradas SET url = REPLACE(url, 'htps://', 'https://') WHERE url LIKE 'htps://%'");
        $db->exec("UPDATE urls_monitoradas SET url = REPLACE(url, 'htp://', 'http://') WHERE url LIKE 'htp://%'");

        // Criar tabela de servidores de email se não existir
        $db->exec("CREATE TABLE IF NOT EXISTS servidores_email (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            host VARCHAR(255) NOT NULL,
            tipo VARCHAR(50) NOT NULL DEFAULT 'SMTP',
            porta INT NOT NULL DEFAULT 587,
            fila_mensagens INT NOT NULL DEFAULT 0,
            mailbox_db VARCHAR(50) NOT NULL DEFAULT 'Mounted',
            transport_svc VARCHAR(50) NOT NULL DEFAULT 'Running',
            active_sync VARCHAR(50) NOT NULL DEFAULT 'Healthy',
            outlook_anywhere VARCHAR(50) NOT NULL DEFAULT 'Healthy',
            dag_replication VARCHAR(50) NOT NULL DEFAULT 'Healthy',
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Semear dados padrão de email se estiver vazia
        $stmtCheckEmail = $db->query("SELECT COUNT(*) FROM servidores_email");
        $countEmail = $stmtCheckEmail->fetchColumn();
        if ($countEmail == 0) {
            $db->exec("INSERT INTO servidores_email (nome, host, tipo, porta, fila_mensagens, mailbox_db, transport_svc, active_sync, outlook_anywhere, dag_replication) 
                       VALUES ('Exchange Local', '127.0.0.1', 'Exchange', 25, 452, 'Mounted', 'Running', 'Healthy', 'Healthy', 'Out of Sync')");
        }
    }

    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        $database = new Database();
        $db = $database->getConnection();
        
        // Auto-migration e seed inicial se necessário
        $this->checkAndInitTable($db);

        // Carregar URLs do banco de dados
        $stmt = $db->query("SELECT * FROM urls_monitoradas ORDER BY id ASC");
        $urls_to_check = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resultados_urls = [];
        foreach ($urls_to_check as $site) {
            $ch = curl_init($site['url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            
            $start_time = microtime(true);
            curl_exec($ch);
            $end_time = microtime(true);
            
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $latency = $http_code ? round(($end_time - $start_time) * 1000) : 0;
            $curl_err = curl_error($ch);
            curl_close($ch);
            
            $status_class = ($http_code >= 200 && $http_code < 400) ? 'bg-success' : 'bg-danger';
            $status_text = $http_code ? $http_code : 'Offline';
            if ($http_code == 200) $status_text = '200 OK';
            
            $resultados_urls[] = [
                'id' => $site['id'],
                'nome' => $site['nome'],
                'url' => $site['url'],
                'status_class' => $status_class,
                'status_text' => $status_text,
                'latency' => $latency . 'ms',
                'uptime' => '100%',
                'curl_error' => $curl_err
            ];
        }

        // Carregar servidores de email do banco
        $stmtEmail = $db->query("SELECT * FROM servidores_email ORDER BY id ASC");
        $email_servers = $stmtEmail->fetchAll(PDO::FETCH_ASSOC);
        
        require 'app/views/layout/header.php';
        require 'app/views/servicemonitor/index.php';
        require 'app/views/layout/footer.php';
    }

    public function store() {
        $database = new Database();
        $db = $database->getConnection();

        // Garante que a tabela esteja inicializada
        $this->checkAndInitTable($db);

        $nome = trim($_POST['nome'] ?? '');
        $url = trim($_POST['url'] ?? '');

        // Auto-corrigir erros de digitação comuns no protocolo (ex: htps:// ou htp://)
        if (preg_match('/^htps:\/\//i', $url)) {
            $url = 'https://' . substr($url, 7);
        } elseif (preg_match('/^htp:\/\//i', $url)) {
            $url = 'http://' . substr($url, 6);
        }

        if (!empty($nome) && !empty($url)) {
            // Validar URL básica
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $query = "INSERT INTO urls_monitoradas (nome, url) VALUES (:nome, :url)";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':nome' => $nome,
                    ':url' => $url
                ]);

                // Gravar log de auditoria se a classe existir
                if (file_exists('app/models/AuditLog.php')) {
                    require_once 'app/models/AuditLog.php';
                    AuditLog::write($db, $_SESSION['usuario_id'] ?? null, 'URL de monitoramento cadastrada', "Nome: $nome, URL: $url");
                }
            }
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        header("Location: " . $base_path . "/services");
        exit;
    }

    public function delete() {
        $database = new Database();
        $db = $database->getConnection();

        $id = intval($_GET['id'] ?? 0);

        if ($id > 0) {
            // Obter detalhes antes de deletar para o log de auditoria
            $stmtDetails = $db->prepare("SELECT nome, url FROM urls_monitoradas WHERE id = :id");
            $stmtDetails->execute([':id' => $id]);
            $details = $stmtDetails->fetch(PDO::FETCH_ASSOC);

            if ($details) {
                $query = "DELETE FROM urls_monitoradas WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([':id' => $id]);

                // Gravar log de auditoria se a classe existir
                if (file_exists('app/models/AuditLog.php')) {
                    require_once 'app/models/AuditLog.php';
                    AuditLog::write($db, $_SESSION['usuario_id'] ?? null, 'URL de monitoramento removida', "Nome: {$details['nome']}, URL: {$details['url']}");
                }
            }
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        header("Location: " . $base_path . "/services");
        exit;
    }

    public function email_store() {
        $database = new Database();
        $db = $database->getConnection();

        // Garante que a tabela esteja inicializada
        $this->checkAndInitTable($db);

        $nome = trim($_POST['nome'] ?? '');
        $host = trim($_POST['host'] ?? '');
        $tipo = trim($_POST['tipo'] ?? 'SMTP');
        $porta = intval($_POST['porta'] ?? 587);
        $fila_mensagens = intval($_POST['fila_mensagens'] ?? 0);
        
        $mailbox_db = trim($_POST['mailbox_db'] ?? 'Mounted');
        $transport_svc = trim($_POST['transport_svc'] ?? 'Running');
        $active_sync = trim($_POST['active_sync'] ?? 'Healthy');
        $outlook_anywhere = trim($_POST['outlook_anywhere'] ?? 'Healthy');
        $dag_replication = trim($_POST['dag_replication'] ?? 'Healthy');

        if (!empty($nome) && !empty($host)) {
            $query = "INSERT INTO servidores_email (nome, host, tipo, porta, fila_mensagens, mailbox_db, transport_svc, active_sync, outlook_anywhere, dag_replication) 
                      VALUES (:nome, :host, :tipo, :porta, :fila_mensagens, :mailbox_db, :transport_svc, :active_sync, :outlook_anywhere, :dag_replication)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':nome' => $nome,
                ':host' => $host,
                ':tipo' => $tipo,
                ':porta' => $porta,
                ':fila_mensagens' => $fila_mensagens,
                ':mailbox_db' => $mailbox_db,
                ':transport_svc' => $transport_svc,
                ':active_sync' => $active_sync,
                ':outlook_anywhere' => $outlook_anywhere,
                ':dag_replication' => $dag_replication
            ]);

            // Gravar log de auditoria se a classe existir
            if (file_exists('app/models/AuditLog.php')) {
                require_once 'app/models/AuditLog.php';
                AuditLog::write($db, $_SESSION['usuario_id'] ?? null, 'Servidor de email cadastrado', "Nome: $nome, Host: $host ($tipo)");
            }
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        header("Location: " . $base_path . "/services");
        exit;
    }

    public function email_delete() {
        $database = new Database();
        $db = $database->getConnection();

        $id = intval($_GET['id'] ?? 0);

        if ($id > 0) {
            // Obter detalhes antes de deletar para o log de auditoria
            $stmtDetails = $db->prepare("SELECT nome, host FROM servidores_email WHERE id = :id");
            $stmtDetails->execute([':id' => $id]);
            $details = $stmtDetails->fetch(PDO::FETCH_ASSOC);

            if ($details) {
                $query = "DELETE FROM servidores_email WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([':id' => $id]);

                // Gravar log de auditoria se a classe existir
                if (file_exists('app/models/AuditLog.php')) {
                    require_once 'app/models/AuditLog.php';
                    AuditLog::write($db, $_SESSION['usuario_id'] ?? null, 'Servidor de email removido', "Nome: {$details['nome']}, Host: {$details['host']}");
                }
            }
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        header("Location: " . $base_path . "/services");
        exit;
    }
}
